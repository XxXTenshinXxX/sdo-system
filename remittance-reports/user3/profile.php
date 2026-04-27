<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../../includes/user-activity.php';

$userRole = $_SESSION['role'] ?? 'staff';
$normalizedUserRole = strtolower(trim($userRole));
$isSuperAdminRole = in_array($normalizedUserRole, ['super admin', 'super_admin', 'superadmin'], true);
$isAdminLikeRole = in_array($normalizedUserRole, ['admin', 'user3', 'admin backup', 'backup admin'], true);
$profileInitial = $isSuperAdminRole ? 'SA' : strtoupper(substr(trim($userRole), 0, 1));
$roleClass = $isSuperAdminRole || $isAdminLikeRole ? 'role-super-admin' : '';
$pageTitle = 'User3 Profile';
$activePage = '';

function user3ProfileRedirectWithFlash(string $message, string $type): never
{
    $_SESSION['profile_flash'] = [
        'message' => $message,
        'type' => $type,
    ];

    header('Location: profile.php');
    exit;
}

function user3ProfileUploadsDirectory(): string
{
    return dirname(__DIR__, 2) . '/uploads/profile-pictures';
}

function user3ProfilePublicPath(string $fileName): string
{
    return 'uploads/profile-pictures/' . $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $connection = userActivityGetConnection();
    userActivityEnsureColumns($connection);
    $currentUserId = (int) $_SESSION['user_id'];
    $formAction = (string) ($_POST['profile_action'] ?? '');

    if ($formAction === 'upload_profile_picture') {
        $upload = $_FILES['profile_picture'] ?? null;
        if (!is_array($upload) || (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            user3ProfileRedirectWithFlash('Please choose an image file to upload.', 'error');
        }

        $imageInfo = @getimagesize((string) ($upload['tmp_name'] ?? ''));
        if ($imageInfo === false) {
            user3ProfileRedirectWithFlash('The selected file is not a valid image.', 'error');
        }

        $mimeType = (string) ($imageInfo['mime'] ?? '');
        $allowedExtensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($allowedExtensions[$mimeType])) {
            user3ProfileRedirectWithFlash('Only JPG, PNG, and WEBP profile pictures are allowed.', 'error');
        }

        if ((int) ($upload['size'] ?? 0) > 5 * 1024 * 1024) {
            user3ProfileRedirectWithFlash('Profile picture must be 5MB or smaller.', 'error');
        }

        $uploadDirectory = user3ProfileUploadsDirectory();
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }

        $existingProfile = userActivityFetchCurrentUser($currentUserId);
        $newFileName = 'user-' . $currentUserId . '-' . bin2hex(random_bytes(4)) . '.' . $allowedExtensions[$mimeType];
        $relativePath = user3ProfilePublicPath($newFileName);
        $absolutePath = $uploadDirectory . '/' . $newFileName;

        if (!move_uploaded_file((string) $upload['tmp_name'], $absolutePath)) {
            user3ProfileRedirectWithFlash('Failed to upload profile picture. Please try again.', 'error');
        }

        $statement = mysqli_prepare($connection, "UPDATE users SET profile_picture = ? WHERE id = ?");
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'si', $relativePath, $currentUserId);
            mysqli_stmt_execute($statement);
            mysqli_stmt_close($statement);
        }

        $oldProfilePicture = trim((string) ($existingProfile['profile_picture'] ?? ''));
        if ($oldProfilePicture !== '' && $oldProfilePicture !== $relativePath) {
            $oldAbsolutePath = dirname(__DIR__, 2) . '/' . ltrim($oldProfilePicture, '/');
            if (is_file($oldAbsolutePath)) {
                @unlink($oldAbsolutePath);
            }
        }

        userActivityLogEvent('profile_picture_update', 'Updated profile picture', ['path' => $relativePath]);
        user3ProfileRedirectWithFlash('Profile picture updated successfully.', 'success');
    }

    if ($formAction === 'change_password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            user3ProfileRedirectWithFlash('Please complete all password fields.', 'error');
        }

        if (strlen($newPassword) < 8) {
            user3ProfileRedirectWithFlash('New password must be at least 8 characters long.', 'error');
        }

        if ($newPassword !== $confirmPassword) {
            user3ProfileRedirectWithFlash('New password and confirmation do not match.', 'error');
        }

        $statement = mysqli_prepare($connection, "SELECT password FROM users WHERE id = ? LIMIT 1");
        $storedPasswordHash = '';
        if ($statement) {
            mysqli_stmt_bind_param($statement, 'i', $currentUserId);
            mysqli_stmt_execute($statement);
            $result = mysqli_stmt_get_result($statement);
            $row = $result ? mysqli_fetch_assoc($result) : null;
            $storedPasswordHash = (string) ($row['password'] ?? '');
            mysqli_stmt_close($statement);
        }

        if ($storedPasswordHash === '' || !password_verify($currentPassword, $storedPasswordHash)) {
            user3ProfileRedirectWithFlash('Current password is incorrect.', 'error');
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $updateStatement = mysqli_prepare($connection, "UPDATE users SET password = ? WHERE id = ?");
        if ($updateStatement) {
            mysqli_stmt_bind_param($updateStatement, 'si', $newPasswordHash, $currentUserId);
            mysqli_stmt_execute($updateStatement);
            mysqli_stmt_close($updateStatement);
        }

        userActivityLogEvent('password_change', 'Changed account password');
        user3ProfileRedirectWithFlash('Password updated successfully.', 'success');
    }
}

userActivityMarkCurrentUser();
$currentProfile = userActivityFetchCurrentUser((int) $_SESSION['user_id']);
$profileFlash = $_SESSION['profile_flash'] ?? ['message' => '', 'type' => ''];
unset($_SESSION['profile_flash']);
$profileImage = trim((string) ($currentProfile['profile_picture'] ?? ''));
$profileImageUrl = $profileImage !== '' ? '../../' . ltrim($profileImage, '/') : '';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<body>
    <div class="layout">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <main class="content">
            <?php include __DIR__ . '/includes/navbar.php'; ?>

            <div class="content-body">
                <section class="section">
                    <div class="panel">
                        <div class="section-header">
                            <h3>Profile</h3>
                            <a href="dashboard.php">Back to Dashboard</a>
                        </div>

                        <?php if (($profileFlash['message'] ?? '') !== ''): ?>
                            <div class="alert-banner alert-<?= htmlspecialchars((string) ($profileFlash['type'] ?? 'success'), ENT_QUOTES, 'UTF-8') ?>">
                                <div class="alert-banner-title"><?= htmlspecialchars((string) $profileFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
                            </div>
                        <?php endif; ?>                       

                        <div class="main-grid" style="margin-top:20px;">
                            <div class="panel profile-tool-panel" style="padding:20px; border:1px solid #e2e8f0;">
                                <div class="section-header" style="margin-bottom:14px;">
                                    <h3>Upload Profile Picture</h3>
                                </div>
                                <form method="POST" enctype="multipart/form-data" class="profile-redesign-form">
                                    <input type="hidden" name="profile_action" value="upload_profile_picture">
                                    
                                    <div class="profile-centered-header">
                                        <div class="profile-avatar-container">
                                            <div class="profile-avatar-main" id="profileUploadAvatarPreview">
                                                <?php if ($profileImageUrl !== ''): ?>
                                                    <img src="<?= htmlspecialchars($profileImageUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Profile picture" class="profile-avatar-image" id="profileUploadPreviewImage">
                                                <?php else: ?>
                                                    <span id="profileUploadPreviewFallback"><?= htmlspecialchars($profileInitial ?? 'U', ENT_QUOTES, 'UTF-8') ?></span>
                                                    <img src="" alt="Profile preview" class="profile-avatar-image" id="profileUploadPreviewImage" hidden>
                                                <?php endif; ?>
                                            </div>
                                            <label for="profilePictureInput" class="profile-avatar-edit-trigger" title="Change Profile Picture">
                                                <i class="fa-solid fa-camera"></i>
                                            </label>
                                        </div>
                                        <div class="profile-info-display">
                                            <h3><?= htmlspecialchars($currentProfile['display_name'] ?? ($userRole ?? 'User'), ENT_QUOTES, 'UTF-8') ?></h3>
                                            <p><?= htmlspecialchars($currentProfile['email'] ?? ($_SESSION['email'] ?? 'No email available'), ENT_QUOTES, 'UTF-8') ?></p>
                                        </div>
                                    </div>

                                    <div class="profile-upload-area">
                                        <label class="profile-upload-dropzone" for="profilePictureInput" id="profileUploadDropzone">
                                            <span class="profile-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                                            <span class="profile-upload-title">Choose a new profile picture</span>
                                            <span class="profile-upload-copy">Drag, drop, or click to browse (JPG, PNG, WEBP)</span>
                                            <span class="profile-upload-file" id="profileUploadFileName">No file selected</span>
                                        </label>
                                    </div>

                                    <label class="profile-form-label sr-only" for="profilePictureInput">Choose image</label>
                                    <input type="file" id="profilePictureInput" name="profile_picture" accept=".jpg,.jpeg,.png,.webp" class="profile-form-input profile-file-input" required>
                                    
                                    <div class="profile-form-footer">
                                        <button type="submit" class="profile-action-btn">
                                            <i class="fa-solid fa-check"></i>
                                            <span>Save Changes</span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="panel" style="padding:20px; border:1px solid #e2e8f0;">
                                <div class="section-header" style="margin-bottom:14px;">
                                    <h3>Change Password</h3>
                                </div>
                                <form method="POST">
                                    <input type="hidden" name="profile_action" value="change_password">
                                    <label class="profile-form-label" for="currentPassword">Current password</label>
                                    <input type="password" id="currentPassword" name="current_password" class="profile-form-input" required>

                                    <label class="profile-form-label" for="newPassword">New password</label>
                                    <input type="password" id="newPassword" name="new_password" class="profile-form-input" required>

                                    <label class="profile-form-label" for="confirmPassword">Confirm new password</label>
                                    <input type="password" id="confirmPassword" name="confirm_password" class="profile-form-input" required>

                                    <button type="submit" class="profile-action-btn profile-action-btn-secondary" style="margin-top:14px;">
                                        <i class="fa-solid fa-key"></i>
                                        <span>Update Password</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <?php include __DIR__ . '/includes/footer.php'; ?>
        </main>
    </div>
    <style>
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .profile-tool-panel {
            background:
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.08), transparent 34%),
                linear-gradient(180deg, #ffffff, #f8fbff);
        }

        .profile-upload-shell {
            display: grid;
            gap: 16px;
        }

        .profile-centered-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 20px 0 30px;
            gap: 20px;
        }

        .profile-avatar-container {
            position: relative;
            padding: 4px;
            background: #ffffff;
            border-radius: 50%;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.15);
        }

        .profile-avatar-main {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1d4ed8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            font-weight: 800;
            overflow: hidden;
            border: 4px solid #ffffff;
        }

        .profile-avatar-edit-trigger {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 36px;
            height: 36px;
            background: #1d4ed8;
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .profile-avatar-edit-trigger:hover {
            transform: scale(1.1);
            background: #2563eb;
        }

        .profile-info-display h3 {
            margin: 0;
            font-size: 20px;
            color: #0f172a;
            font-weight: 800;
        }

        .profile-info-display p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .profile-upload-area {
            margin-bottom: 20px;
        }

        .profile-upload-dropzone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 160px;
            padding: 24px;
            border: 2px dashed #cbd5e1;
            border-radius: 20px;
            background: #f8fafc;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .profile-upload-dropzone:hover,
        .profile-upload-dropzone.is-ready {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .profile-upload-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #dbeafe;
            color: #1d4ed8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 4px;
        }

        .profile-upload-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .profile-upload-copy {
            font-size: 13px;
            color: #64748b;
        }

        .profile-upload-file {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 700;
            color: #3b82f6;
            background: #ffffff;
            padding: 4px 12px;
            border-radius: 999px;
            border: 1px solid #dbeafe;
        }

        .profile-form-footer {
            display: flex;
            justify-content: center;
            padding-top: 10px;
        }

        .profile-form-label {
            display: block;
            margin: 12px 0 8px;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
        }

        .profile-form-input {
            width: 100%;
            min-height: 46px;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 12px 14px;
            font-size: 14px;
            color: #0f172a;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .profile-form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .profile-file-input {
            display: none;
        }

        .profile-form-help {
            margin-top: 8px;
            font-size: 12px;
            color: #64748b;
        }

        .profile-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 16px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 14px 24px rgba(37, 99, 235, 0.18);
        }

        .profile-action-btn-secondary {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            box-shadow: 0 14px 24px rgba(20, 184, 166, 0.18);
        }
    </style>
    <script>
        (function () {
            const fileInput = document.getElementById('profilePictureInput');
            const fileNameLabel = document.getElementById('profileUploadFileName');
            const dropzone = document.getElementById('profileUploadDropzone');
            const previewImage = document.getElementById('profileUploadPreviewImage');
            const fallbackLabel = document.getElementById('profileUploadPreviewFallback');

            if (!fileInput || !fileNameLabel || !dropzone || !previewImage) {
                return;
            }

            fileInput.addEventListener('change', function () {
                const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

                if (!file) {
                    fileNameLabel.textContent = 'No file selected';
                    dropzone.classList.remove('is-ready');
                    return;
                }

                fileNameLabel.textContent = file.name;
                dropzone.classList.add('is-ready');

                const objectUrl = URL.createObjectURL(file);
                previewImage.src = objectUrl;
                previewImage.hidden = false;
                if (fallbackLabel) {
                    fallbackLabel.hidden = true;
                }
            });
        }());
    </script>
</body>
</html>
