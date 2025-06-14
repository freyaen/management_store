<?php
function isActive($path = '') {
    $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    if ($path == '') {
        return $currentPath === '' ? 'active' : '';
    }
    return strpos($currentPath, $path) !== false ? 'active' : '';
}

$userRole = $_COOKIE['role_id'] ?? 0;
?>

<div class="navigation">
    <div class="navigation-header">
        <div>
            <span>Navigation</span>
        </div>
        <a href="#"><i class="ti-close"></i></a>
    </div>

    <div class="navigation-menu-body mx-auto">
        <ul style="margin: auto;">

            <!-- Dashboard -->
            <li>
                <a class="<?= isActive('') ?>" href="<?= getDomainUrl() ?>">
                    <span class="nav-link-icon">
                        <i data-feather="home"></i>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if($userRole == 2): ?>
                <!-- Products -->
                <li>
                    <a class="<?= isActive('products') ?>" href="<?= getDomainUrl() . 'products' ?>">
                        <span class="nav-link-icon">
                            <i data-feather="package"></i>
                        </span>
                        <span>Products</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Requests -->
            <li>
                <a class="<?= isActive('requests') ?>" href="<?= getDomainUrl() . 'requests' ?>">
                    <span class="nav-link-icon">
                        <i data-feather="file-text"></i>
                    </span>
                    <span>Requests</span>
                </a>
            </li>

            <!-- Returns -->
            <li>
                <a class="<?= isActive('returns') ?>" href="<?= getDomainUrl() . 'returns' ?>">
                    <span class="nav-link-icon">
                        <i data-feather="corner-up-left"></i>
                    </span>
                    <span>Returns</span>
                </a>
            </li>

            <?php if($userRole == 2): ?>
                <!-- Customer Sales -->
                <li>
                    <a class="<?= isActive('sales') ?>" href="<?= getDomainUrl() . 'sales' ?>">
                        <span class="nav-link-icon">
                            <i data-feather="shopping-cart"></i>
                        </span>
                        <span>Customer Sales</span>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </div>
</div>
