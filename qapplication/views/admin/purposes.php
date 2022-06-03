<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: chamith
 * Date: 3/11/18
 * Time: 4:17 AM
 */
?>
<br>
<h4 class="text-center">Purposes</h4>
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
            <table class="table table-hover table-striped table-bordered">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Type</th>
                    <th scope="col">Created On</th>
                    <th scope="col">Created By</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($purposes as $purpose){ ?>
                    <tr>
                        <th scope="row"><?php echo $purpose->id; ?></th>
                        <td><?php echo $purpose->name; ?></td>
                        <td><?php echo $purpose->type; ?></td>
                        <td><?php echo $purpose->created_on; ?></td>
                        <td><?php echo $purpose->created_by; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-1"></div>
    </div>
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <a href="/admin/add_purpose">
                <button type="button" class="btn btn-primary col-md-6">Add new purpose</button>
            </a>
        </div>
        <div class="col-md-1"></div>
    </div>
</div>
