<?php 
include 'layouts/head.php';
include 'config/database.php';

?>

<div class="layout-wrapper">
    <?php include 'layouts/header.php'; ?>

    <div class="content-wrapper">
        <?php include 'layouts/navbar.php'; ?>

        <div class="content-body">
            <div class="content">
                <div class="page-header">
                    <h3>Dashboard</h3>
                </div>

                <?php
                    switch ($userRole) {
                        case 1:
                            include 'dashboards/warehouse.php';
                            break;
                        default:
                            include 'dashboards/store.php';
                            break;
                    }
                ?>
            </div>

            <?php include 'layouts/footer.php'; ?>
        </div>
    </div>
</div>

<?php include 'layouts/tail.php'; ?>