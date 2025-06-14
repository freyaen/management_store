<?php 
$userName = $_COOKIE['name'] ?? '';
$userRoleName = $_COOKIE['role_name'] ?? '';
?>

<div class="header d-print-none">
    <div class="header-container">

        <!-- Left Section -->
        <div class="header-left">
            <div class="navigation-toggler">
                <a href="" data-action="navigation-toggler">
                    <i data-feather="menu"></i>
                </a>
            </div>
            <div class="header-logo">
                <a href="">
                    <img width="150" class="logo" src="../../assets/images/logo.png" alt="logo">
                </a>
            </div>
        </div>

        <!-- Center Section -->
        <div class="header-body">
            <div class="header-body-left">
                <!-- Optional: Add search bar or breadcrumbs here -->
            </div>
            <div class="header-body-right">
                <ul class="navbar-nav ">
                    <li class="nav-item d-flex">
                        <a href="#" class="nav-link" title="User menu" data-toggle="dropdown">
                            <span class="ml-2 d-sm-inline d-none">
                                <?= $userName ?> (<?= $userRoleName ?>)
                            </span>
                        </a>
                        <a href="<?= getDomainUrl() . 'logout.php' ?>" class="btn btn-danger btn-small"><i class='bx bx-log-out-circle text-white' ></i></a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Section -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item header-toggler">
                <a href="#" class="nav-link">
                    <i data-feather="arrow-down"></i>
                </a>
            </li>
        </ul>

    </div>
</div>