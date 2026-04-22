<nav class="navbar">
    <div class="brand">
        <div class="brand-badge">
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div>
            <h1>Leave Monitoring</h1>
            <p>Track, review, and manage leave</p>
        </div>
    </div>

    <div class="nav-actions">
        <div class="nav-chip <?= htmlspecialchars($roleClass, ENT_QUOTES, 'UTF-8') ?>">
            <span><?= htmlspecialchars(ucwords($userRole), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <button type="button" class="profile-button" aria-label="Profile">
            <?= htmlspecialchars($profileInitial, ENT_QUOTES, 'UTF-8') ?>
        </button>
    </div>
</nav>
