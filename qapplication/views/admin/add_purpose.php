<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 11/3/18
 * Time: 9:17 AM
 */
?>
<br>
<h4 class="text-center">Administrator: Add Purpose</h4>
<p class="lead text-center">Create new purpose</p>
<div class="container">
    <div class="row">
        <div class="col-md-2">
        </div>
        <div id="alert-box" class="col-sm-8">
            <?php if($this->session->flashdata('error_message')){ ?>
                <div class="alert alert-danger" role="alert"><?php echo $this->session->flashdata('error_message'); ?></div>
            <?php } ?>
            <?php if($this->session->flashdata('success_message')){ ?>
                <div class="alert alert-success" role="alert"><?php echo $this->session->flashdata('success_message'); ?></div>
            <?php } ?>
        </div>
        <div class="col-md-2">
        </div>
    </div>
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <form id="add_purpose" class="add_purpose_form" method="post" action="/admin/add_purpose" novalidate>
                <div class="form-group">
                    <label for="name">Purpose Name</label>
                    <input type="text" class="form-control highlight-input" id="purpose_name" name="purpose_name" aria-describedby="purpose_name_help" placeholder="" minlength="2" maxlength="40" required>
                    <input type="hidden" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                    <div class="invalid-feedback">Please enter a valid name</div>
                    <small id="purpose_name_help" class="form-text text-muted">Purpose to be shown at get token form</small>
                </div>
                <div class="form-group">
                    <input type="submit" id="add_purpose" class="btn btn-primary col-md-6" value="Submit">
                </div>
            </form>
        </div>
        <div class="col-md-1"></div>
    </div>
</div>
