<?php
$activePage = $activePage ?? '';
$esPages = ['es-active', 'es-inactivation', 'es-separation'];
$secPages = ['sec-active', 'sec-inactivation', 'sec-separation'];
$isEsOpen = in_array($activePage, $esPages, true);
$isSecOpen = in_array($activePage, $secPages, true);
?>
<aside class="sidebar">
    <div>
        <div class="sidebar-brand">
            <img src="../assets/images/SDO-Logo.png" alt="School Division Office Logo" class="sidebar-brand-logo">
            <div class="sidebar-brand-copy">
                <strong>School Division Office</strong>
                <span>Leave Monitoring System</span>
            </div>
            <br>
        </div>
        <p class="sidebar-section-title">Main Menu</p>
        <nav class="menu">
            <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
            <details class="menu-group" <?= $isEsOpen ? 'open' : '' ?>>
                <summary class="<?= $isEsOpen ? 'active' : '' ?>">
                    <span><i class="fa-solid fa-users"></i> ES</span>
                    <i class="fa-solid fa-chevron-down menu-caret"></i>
                </summary>
                <div class="submenu">
                    <a href="es-active.php" class="<?= $activePage === 'es-active' ? 'active' : '' ?>">Active</a>
                    <a href="es-inactivation.php" class="<?= $activePage === 'es-inactivation' ? 'active' : '' ?>">Inactivation</a>
                    <a href="es-separation.php" class="<?= $activePage === 'es-separation' ? 'active' : '' ?>">Separation</a>
                </div>
            </details>
            <details class="menu-group" <?= $isSecOpen ? 'open' : '' ?>>
                <summary class="<?= $isSecOpen ? 'active' : '' ?>">
                    <span><i class="fa-solid fa-building-columns"></i> SEC</span>
                    <i class="fa-solid fa-chevron-down menu-caret"></i>
                </summary>
                <div class="submenu">
                    <a href="sec-active.php" class="<?= $activePage === 'sec-active' ? 'active' : '' ?>">Active</a>
                    <a href="sec-inactivation.php" class="<?= $activePage === 'sec-inactivation' ? 'active' : '' ?>">Inactivation</a>
                    <a href="sec-separation.php" class="<?= $activePage === 'sec-separation' ? 'active' : '' ?>">Separation</a>
                </div>
            </details>
            <a href="../select-dashboard.php"><i class="fa-solid fa-arrow-left"></i> Back to Systems</a>
        </nav>
    </div>
</aside>
