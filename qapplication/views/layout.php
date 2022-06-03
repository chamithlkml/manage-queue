<?php
/**
 * Created by Dropsuite Pte Ltd.
 * User: chamith
 * Date: 8/12/18
 * Time: 11:50 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="<?php echo base_url(); ?>css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>css/bootstrap-datepicker3.standalone.min.css">
    <?php if(isset($styles)){ ?>
        <?php foreach($styles as $style_path){ ?>
            <script rel="stylesheet" href="<?php echo base_url() . $style_path . "?t=" . time(); ?>"></script>
        <?php } ?>
    <?php } ?>
    <link rel="stylesheet" href="<?php echo base_url(); ?>css/site.css?t=<?php echo time(); ?>">
    <title>ManageQ</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <a class="navbar-brand" href="/">ManageQ</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <?php if( ! isset($officer)){ ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Administrator
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <?php if(isset($admin)){ ?>
                            <a class="dropdown-item" href="/admin/add_officers">Add Officer</a>
                            <a class="dropdown-item" href="/admin/list_officers">List Officers</a>
                            <a class="dropdown-item" href="/admin/purposes">Purposes</a>
                            <a class="dropdown-item" href="/admin/waiting_points">Waiting Points</a>
                            <a class="dropdown-item" href="/admin/logout">Sign Out</a>
                        <?php }else{ ?>
                            <a class="dropdown-item" href="/admin/login">Sign In</a>
                        <?php } ?>
                    </div>
                </li>
            <?php } ?>
            <?php if( ! isset($admin)){ ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Officer
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <?php if(isset($officer)){ ?>
                            <a class="dropdown-item" href="/official/index">Dashboard</a>
                            <a class="dropdown-item" href="/official/logout">Sign Out</a>
                        <?php }else{ ?>
                            <a class="dropdown-item" href="/official/login">Sign In</a>
                        <?php } ?>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
</nav>
<?php $this->load->view($content); ?>
<script type="text/javascript" src="<?php echo base_url(); ?>js/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>js/popper.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>js/scripts/layout.js?t=<?php echo time(); ?>"></script>
<?php if(isset($scripts)){ ?>
    <?php foreach($scripts as $script_path){ ?>
        <script type="text/javascript" src="<?php echo base_url() . $script_path; ?>"></script>
    <?php } ?>
<?php } ?>
</body>
</html>
