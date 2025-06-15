<?php

$userRole = $_COOKIE['role_id'] ?? 0;
?>

<div class="navigation">
    <div class="navigation-header">
        <div>
            <span>Navigation</span>
        </div>
        <a href="#"><i class="bx bx-x"></i></a>
    </div>

    <div class="navigation-menu-body mx-auto">
        <ul style="margin: auto;">

            <!-- Dashboard -->
            <li>
                <a class="<?= isActive('', true) ?>" href="<?= getDomainUrl() ?>">
                    <span class="nav-link-icon">
                        <i class="bx bx-home"></i>
                    </span>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if($userRole == 2): ?>
                <!-- Products -->
                <li>
                    <a class="<?= isActive('products') ?>" href="<?= getDomainUrl() . 'products' ?>">
                        <span class="nav-link-icon">
                            <i class="bx bx-box"></i>
                        </span>
                        <span>Products</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Requests -->
            <li>
                <a class="<?= isActive('requests') ?>" href="<?= getDomainUrl() . 'requests' ?>">
                    <span class="nav-link-icon">
                        <i class="bx bx-file"></i>
                    </span>
                    <span>Requests</span>
                </a>
            </li>

            <!-- Returns -->
            <li>
                <a class="<?= isActive('returns') ?>" href="<?= getDomainUrl() . 'returns' ?>">
                    <span class="nav-link-icon">
                        <i class="bx bx-undo"></i>
                    </span>
                    <span>Returns</span>
                </a>
            </li>

            <?php if($userRole == 2): ?>
                <!-- Customer Sales -->
                <li>
                    <a class="<?= isActive('sales') ?>" href="<?= getDomainUrl() . 'sales' ?>">
                        <span class="nav-link-icon">
                            <i class="bx bx-cart"></i>
                        </span>
                        <span>Customer Sales</span>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </div>
</div>
