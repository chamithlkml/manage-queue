<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by ManageQ.
 * User: chamith
 * Date: 22/9/18
 * Time: 5:03 AM
 */
?>
<br>
<h4 class="text-center">List of Active Officers</h4>
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
        <div class="col-md-1">
        </div>
        <div id="alert-box" class="col-sm-10">
            <table class="table table-hover table-striped table-bordered" id="list_officers_tbl" style="width:100%">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Role</th>
                        <th scope="col">Mobile No</th>
                        <th scope="col">Started Date</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($officers as $officer){ ?>
                    <tr>
                        <td><?php echo $officer->name; ?></td>
                        <td><?php echo $officer->role; ?></td>
                        <td><?php echo $officer->mobile_number; ?></td>
                        <td><?php echo $officer->created_on; ?></td>
                        <td>
                            <?php if($officer->is_deletable){ ?>
                                <form action="/admin/delete_officer_confirm" method="post">
                                    <input type="submit" id="" class="btn btn-danger" value="Delete">
                                </form>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-1">
        </div>
    </div>
</div>
