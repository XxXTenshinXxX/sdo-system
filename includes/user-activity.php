<?php

function userActivityGetConnection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    require __DIR__ . '/../config/db.php';

    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new RuntimeException('Database connection is unavailable.');
    }

    $connection = $conn;

    return $connection;
}

function userActivityEnsureColumns(mysqli $connection): void
{
    static $isReady = false;

    if ($isReady) {
        return;
    }

    $requiredColumns = [
        'last_login_at' => "ALTER TABLE users ADD COLUMN last_login_at DATETIME NULL",
        'last_activity_at' => "ALTER TABLE users ADD COLUMN last_activity_at DATETIME NULL",
        'profile_picture' => "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL",
    ];

    foreach ($requiredColumns as $columnName => $alterSql) {
        $escapedColumnName = mysqli_real_escape_string($connection, $columnName);
        $columnResult = mysqli_query($connection, "SHOW COLUMNS FROM users LIKE '{$escapedColumnName}'");

        if ($columnResult instanceof mysqli_result && mysqli_num_rows($columnResult) > 0) {
            mysqli_free_result($columnResult);
            continue;
        }

        if ($columnResult instanceof mysqli_result) {
            mysqli_free_result($columnResult);
        }

        mysqli_query($connection, $alterSql);
    }

    $isReady = true;
}

function userActivityEnsureAuditTable(mysqli $connection): void
{
    static $isReady = false;

    if ($isReady) {
        return;
    }

    mysqli_query(
        $connection,
        "CREATE TABLE IF NOT EXISTS system_activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            user_email VARCHAR(255) NULL,
            user_role VARCHAR(100) NULL,
            action_name VARCHAR(120) NOT NULL,
            description TEXT NOT NULL,
            page_path VARCHAR(255) NULL,
            context_json LONGTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $isReady = true;
}

function userActivityEnsureNotificationsTable(mysqli $connection): void
{
    static $isReady = false;

    if ($isReady) {
        return;
    }

    mysqli_query(
        $connection,
        "CREATE TABLE IF NOT EXISTS system_notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            target_role VARCHAR(100) NULL,
            type VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            is_broadcast TINYINT(1) DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $isReady = true;
}

function userActivityNormalizeRole(string $role): string
{
    return strtolower(trim($role));
}

function userActivityIsAdminViewer(string $role): bool
{
    return in_array(userActivityNormalizeRole($role), ['super admin', 'super_admin', 'superadmin', 'admin', 'user3', 'admin backup', 'backup admin'], true);
}

function userActivityMarkCurrentUser(bool $recordLogin = false): void
{
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $connection = userActivityGetConnection();
    userActivityEnsureColumns($connection);

    $userId = (int) $_SESSION['user_id'];

    if ($recordLogin) {
        $statement = mysqli_prepare($connection, "UPDATE users SET last_login_at = NOW(), last_activity_at = NOW() WHERE id = ?");
    } else {
        $statement = mysqli_prepare($connection, "UPDATE users SET last_activity_at = NOW() WHERE id = ?");
    }

    if (!$statement) {
        return;
    }

    mysqli_stmt_bind_param($statement, 'i', $userId);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
}

function userActivityCurrentPath(): string
{
    $path = (string) ($_SERVER['REQUEST_URI'] ?? '');
    if ($path !== '') {
        return $path;
    }

    return (string) ($_SERVER['PHP_SELF'] ?? '');
}

function userActivityLogEvent(string $actionName, string $description, array $context = []): void
{
    $connection = userActivityGetConnection();
    userActivityEnsureColumns($connection);
    userActivityEnsureAuditTable($connection);

    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    $userEmail = isset($_SESSION['email']) ? (string) $_SESSION['email'] : null;
    $userRole = isset($_SESSION['role']) ? (string) $_SESSION['role'] : null;
    $pagePath = userActivityCurrentPath();
    $contextJson = $context !== [] ? json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null;

    $statement = mysqli_prepare(
        $connection,
        "INSERT INTO system_activity_logs (user_id, user_email, user_role, action_name, description, page_path, context_json)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$statement) {
        return;
    }

    mysqli_stmt_bind_param(
        $statement,
        'issssss',
        $userId,
        $userEmail,
        $userRole,
        $actionName,
        $description,
        $pagePath,
        $contextJson
    );
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
}

function userActivityLogPageVisit(string $label): void
{
    static $alreadyLogged = false;

    if ($alreadyLogged) {
        return;
    }

    $alreadyLogged = true;
    userActivityLogEvent('page_visit', 'Visited ' . $label, ['label' => $label]);
}

function userActivityMarkUserOffline(?int $userId = null): void
{
    $resolvedUserId = $userId ?? (isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0);

    if ($resolvedUserId <= 0) {
        return;
    }

    $connection = userActivityGetConnection();
    userActivityEnsureColumns($connection);

    $statement = mysqli_prepare($connection, "UPDATE users SET last_activity_at = DATE_SUB(NOW(), INTERVAL 10 MINUTE) WHERE id = ?");

    if (!$statement) {
        return;
    }

    mysqli_stmt_bind_param($statement, 'i', $resolvedUserId);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
}

function userActivityFetchAuditEntries(int $limit = 100): array
{
    $connection = userActivityGetConnection();
    userActivityEnsureColumns($connection);
    userActivityEnsureAuditTable($connection);

    $resolvedLimit = max(1, min(500, $limit));
    $query = "
        SELECT id, user_id, user_email, user_role, action_name, description, page_path, context_json, created_at
        FROM system_activity_logs
        ORDER BY created_at DESC, id DESC
        LIMIT {$resolvedLimit}
    ";

    $result = mysqli_query($connection, $query);
    if (!$result) {
        return [];
    }

    $entries = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $entries[] = [
            'id' => (int) ($row['id'] ?? 0),
            'user_label' => trim((string) ($row['user_email'] ?? '')) !== '' ? (string) $row['user_email'] : ('User #' . (int) ($row['user_id'] ?? 0)),
            'role_label' => userActivityRoleLabel((string) ($row['user_role'] ?? '')),
            'action_name' => (string) ($row['action_name'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'page_path' => (string) ($row['page_path'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'created_at_formatted' => userActivityFormatDateTime((string) ($row['created_at'] ?? '')),
        ];
    }

    mysqli_free_result($result);

    return $entries;
}

function userActivityDisplayName(array $user): string
{
    $role = userActivityNormalizeRole((string) ($user['role'] ?? ''));
    $email = trim((string) ($user['email'] ?? ''));

    return match ($role) {
        'super admin', 'super_admin', 'superadmin' => 'Super Admin',
        'user1' => 'User 1',
        'user2' => 'User 2',
        'user3', 'admin backup', 'backup admin', 'admin' => 'Backup Administrator',
        default => $email !== '' ? $email : 'User #' . (int) ($user['id'] ?? 0),
    };
}

function userActivityRoleLabel(string $role): string
{
    $normalizedRole = userActivityNormalizeRole($role);

    return match ($normalizedRole) {
        'super admin', 'super_admin', 'superadmin' => 'Super Admin',
        'user1' => 'User 1',
        'user2' => 'User 2',
        'user3', 'admin backup', 'backup admin', 'admin' => 'Backup Administrator',
        'staff' => 'Staff',
        default => trim($role) !== '' ? ucwords(str_replace('_', ' ', trim($role))) : 'Unassigned',
    };
}

function userActivityRoleBadgeClass(string $role): string
{
    return match (userActivityNormalizeRole($role)) {
        'super admin', 'super_admin', 'superadmin' => 'approved',
        'user3', 'admin backup', 'backup admin', 'admin' => 'review',
        default => 'pending',
    };
}

function userActivityAccessLabel(string $role): string
{
    return match (userActivityNormalizeRole($role)) {
        'super admin', 'super_admin', 'superadmin' => 'Full access to all system dashboards',
        'user1' => 'ES / SHS access',
        'user2' => 'QES access',
        'user3', 'admin backup', 'backup admin', 'admin' => 'Full backup administration access for remittance monitoring',
        default => 'Assigned system access',
    };
}

function userActivityDashboardLabel(string $role): string
{
    return match (userActivityNormalizeRole($role)) {
        'super admin', 'super_admin', 'superadmin' => 'Admin / User 3 Backup',
        'user1' => 'User 1',
        'user2' => 'User 2',
        'user3', 'admin backup', 'backup admin', 'admin' => 'Backup Admin Dashboard',
        default => 'Not assigned',
    };
}

function userActivityRelativeTime(?string $timestamp): string
{
    if ($timestamp === null || trim($timestamp) === '') {
        return 'No activity yet';
    }

    $activityTime = strtotime($timestamp);
    if ($activityTime === false) {
        return 'No activity yet';
    }

    $delta = time() - $activityTime;
    if ($delta < 0) {
        $delta = 0;
    }

    if ($delta < 60) {
        return 'just now';
    }

    if ($delta < 3600) {
        return floor($delta / 60) . ' minute(s) ago';
    }

    if ($delta < 86400) {
        return floor($delta / 3600) . ' hour(s) ago';
    }

    return floor($delta / 86400) . ' day(s) ago';
}

function userActivityFetchUsersSnapshot(): array
{
    $connection = userActivityGetConnection();
    userActivityEnsureColumns($connection);

    $hasCreatedAt = false;
    $createdAtResult = mysqli_query($connection, "SHOW COLUMNS FROM users LIKE 'created_at'");
    if ($createdAtResult instanceof mysqli_result) {
        $hasCreatedAt = mysqli_num_rows($createdAtResult) > 0;
        mysqli_free_result($createdAtResult);
    }

    $query = "
        SELECT id, email, role, " . ($hasCreatedAt ? "created_at" : "NULL AS created_at") . ", last_login_at, last_activity_at, profile_picture
        FROM users
        ORDER BY id ASC
    ";

    $result = mysqli_query($connection, $query);

    if (!$result) {
        return [];
    }

    $users = [];
    $activeThreshold = time() - 75;

    while ($row = mysqli_fetch_assoc($result)) {
        $lastActivityAt = (string) ($row['last_activity_at'] ?? '');
        $lastActivityTimestamp = $lastActivityAt !== '' ? strtotime($lastActivityAt) : false;
        $isActive = $lastActivityTimestamp !== false && $lastActivityTimestamp >= $activeThreshold;

        $users[] = [
            'id' => (int) ($row['id'] ?? 0),
            'email' => (string) ($row['email'] ?? ''),
            'display_name' => userActivityDisplayName($row),
            'role' => userActivityRoleLabel((string) ($row['role'] ?? '')),
            'role_badge_class' => userActivityRoleBadgeClass((string) ($row['role'] ?? '')),
            'access' => userActivityAccessLabel((string) ($row['role'] ?? '')),
            'dashboard' => userActivityDashboardLabel((string) ($row['role'] ?? '')),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'last_login_at' => (string) ($row['last_login_at'] ?? ''),
            'last_activity_at' => $lastActivityAt,
            'profile_picture' => (string) ($row['profile_picture'] ?? ''),
            'is_active' => $isActive,
            'status_label' => $isActive ? 'Active now' : 'Inactive',
            'status_badge_class' => $isActive ? 'approved' : 'review',
            'last_seen_label' => $isActive ? 'Receiving live heartbeat' : userActivityRelativeTime($lastActivityAt),
        ];
    }

    mysqli_free_result($result);

    return $users;
}

function userActivityFormatDateTime(?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') {
        return 'Not available';
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
        return 'Not available';
    }

    return date('M j, Y h:i A', $timestamp);
}

function userActivityFetchCurrentUser(int $userId): ?array
{
    $connection = userActivityGetConnection();
    userActivityEnsureColumns($connection);

    $hasCreatedAt = false;
    $createdAtResult = mysqli_query($connection, "SHOW COLUMNS FROM users LIKE 'created_at'");
    if ($createdAtResult instanceof mysqli_result) {
        $hasCreatedAt = mysqli_num_rows($createdAtResult) > 0;
        mysqli_free_result($createdAtResult);
    }

    $query = "SELECT id, email, role, " . ($hasCreatedAt ? "created_at" : "NULL AS created_at") . ", last_login_at, last_activity_at, profile_picture FROM users WHERE id = ? LIMIT 1";
    $statement = mysqli_prepare($connection, $query);

    if (!$statement) {
        return null;
    }

    mysqli_stmt_bind_param($statement, 'i', $userId);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($statement);

    if (!$row) {
        return null;
    }

    $lastActivityAt = (string) ($row['last_activity_at'] ?? '');
    $lastActivityTimestamp = $lastActivityAt !== '' ? strtotime($lastActivityAt) : false;
    $isActive = $lastActivityTimestamp !== false && $lastActivityTimestamp >= (time() - 75);

    return [
        'id' => (int) ($row['id'] ?? 0),
        'email' => (string) ($row['email'] ?? ''),
        'display_name' => userActivityDisplayName($row),
        'role' => userActivityRoleLabel((string) ($row['role'] ?? '')),
        'access' => userActivityAccessLabel((string) ($row['role'] ?? '')),
        'dashboard' => userActivityDashboardLabel((string) ($row['role'] ?? '')),
        'created_at' => (string) ($row['created_at'] ?? ''),
        'created_at_formatted' => userActivityFormatDateTime((string) ($row['created_at'] ?? '')),
        'last_login_at' => (string) ($row['last_login_at'] ?? ''),
        'last_login_at_formatted' => userActivityFormatDateTime((string) ($row['last_login_at'] ?? '')),
        'last_activity_at' => $lastActivityAt,
        'last_activity_at_formatted' => userActivityFormatDateTime($lastActivityAt),
        'profile_picture' => (string) ($row['profile_picture'] ?? ''),
        'is_active' => $isActive,
        'last_seen_label' => $isActive ? 'Receiving live heartbeat' : userActivityRelativeTime($lastActivityAt),
    ];
}

function userActivityNotify(string $type, string $message, ?string $targetRole = null): void
{
    $connection = userActivityGetConnection();
    userActivityEnsureNotificationsTable($connection);

    $statement = mysqli_prepare(
        $connection,
        "INSERT INTO system_notifications (type, message, target_role, is_broadcast) VALUES (?, ?, ?, ?)"
    );

    if (!$statement) {
        return;
    }

    $isBroadcast = $targetRole === null ? 1 : 0;
    $type = substr($type, 0, 50);
    $targetRole = $targetRole !== null ? userActivityNormalizeRole($targetRole) : null;

    mysqli_stmt_bind_param($statement, 'sssi', $type, $message, $targetRole, $isBroadcast);
    mysqli_stmt_execute($statement);
    mysqli_stmt_close($statement);
}

function userActivityFetchNotifications(?string $role = null, int $limit = 10): array
{
    $connection = userActivityGetConnection();
    userActivityEnsureNotificationsTable($connection);

    $sql = "SELECT id, type, message, created_at FROM system_notifications ";
    if ($role !== null) {
        $normalizedRole = userActivityNormalizeRole($role);
        // Map common admin roles to search correctly
        $roleMatch = "target_role = '{$normalizedRole}'";
        if (in_array($normalizedRole, ['admin', 'user3', 'super admin'], true)) {
             $roleMatch = "target_role IN ('admin', 'user3', 'super admin')";
        }
        $sql .= "WHERE is_broadcast = 1 OR {$roleMatch} ";
    } else {
        $sql .= "WHERE is_broadcast = 1 ";
    }
    
    $sql .= "ORDER BY created_at DESC LIMIT " . (int)$limit;

    $result = mysqli_query($connection, $sql);
    if (!$result) {
        return [];
    }

    $notifications = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $notifications[] = [
            'id' => (int) $row['id'],
            'type' => (string) $row['type'],
            'message' => (string) $row['message'],
            'created_at' => (string) $row['created_at'],
            'created_at_formatted' => userActivityRelativeTime((string) $row['created_at']),
            'dot_class' => match (strtolower((string) $row['type'])) {
                'upload', 'success' => 'is-success',
                'delete', 'danger', 'error' => 'is-danger',
                'request', 'warn', 'warning' => 'is-warn',
                default => 'is-info'
            }
        ];
    }

    mysqli_free_result($result);
    return $notifications;
}
