<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 11/4/18
 * Time: 4:44 AM
 */
?>
<br>
<h4 class="text-center">Waiting Points</h4>
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
            <table id="waiting_points" class="table table-hover table-striped table-bordered">
                <thead class="thead-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Purpose</th>
                    <th scope="col">Waiting Point On</th>
                    <th scope="col">Duration (minutes)</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($waiting_points as $wp){ ?>
                    <tr>
                        <th scope="row"><?php echo $wp->id; ?></th>
                        <td><?php echo $wp->name; ?></td>
                        <td><?php echo $wp->purpose_id; ?></td>
                        <td><?php echo $wp->waiting_point_on; ?></td>
                        <td><?php echo $wp->duration; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-1"></div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <a href="/admin/add_waiting_point">
                <button type="button" class="btn btn-primary col-md-6">Add new waiting point</button>
            </a>
        </div>
        <div class="col-md-1"></div>
    </div>
</div>
