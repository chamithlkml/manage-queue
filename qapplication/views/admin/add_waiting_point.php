<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 11/4/18
 * Time: 5:16 AM
 */
?>
<br>
<h4 class="text-center">Administrator: Add waiting point</h4>
<p class="lead text-center">Create new waiting point</p>
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
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <form id="add_waiting_point" class="add_waiting_point_form" method="post" action="/admin/add_waiting_point" novalidate>
                <input type="hidden" id="get_token_csrf_value" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                <div class="form-group">
                    <label for="waiting_point_name">Waiting Point Name</label>
                    <input type="text" class="form-control highlight-input" name="waiting_point_name" id="waiting_point_name" aria-describedby="waiting_point_name_help" minlength="2" maxlength="25" required>
                    <div class="invalid-feedback">Need a valid name with 2-25 character length</div>
                    <small id="waiting_point_name_help" class="form-text text-muted">For reference only</small>
                </div>
                <div class="form-group">
                    <label for="name">Purpose</label>
                    <select class="form-control highlight-input" id="purpose_id" name="purpose_id" aria-describedby="purpose_help" required>
                        <option value="">Select</option>
                        <?php foreach($purposes as $purpose){ ?>
                            <option value="<?php echo $purpose->id; ?>"><?php echo $purpose->name; ?></option>
                        <?php } ?>
                        <div class="invalid-feedback">Need a valid purpose</div>
                    </select>
                </div>
                <div class="form-group">
                    <label for="preferred_date">Date</label>
                    <input type="text" class="form-control highlight-input" id="date_of_waiting_point" name="date_of_waiting_point" value="<?php echo date("Y-m-d"); ?>" required>
                    <div class="invalid-feedback">Please enter the date you wish to set waiting point</div>
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-th"></span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="form-row">
                        <div class="col-md-5 mb-3">
                            <label for="time_hour">Hour</label>
                            <select class="form-control highlight-input" id="time_hour" name="time_hour" required>
                                <option value="">Select</option>
                                <?php foreach($hour_entries as $hour_entry){ ?>
                                    <option value="<?php echo $hour_entry; ?>"><?php echo $hour_entry; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label for="time_hour">Minute</label>
                            <select class="form-control highlight-input" id="time_minute" name="time_minute" required>
                                <option value="">Select</option>
                                <?php foreach($minute_entries as $minute_entry){ ?>
                                <option value="<?php echo $minute_entry; ?>"><?php echo $minute_entry; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="time_hour">AM/PM</label>
                            <select class="form-control highlight-input" id="time_meridiem" name="time_meridiem" required>
                                <?php foreach($meridiem_entries as $meridiem_entry){ ?>
                                <option value="<?php echo $meridiem_entry; ?>"><?php echo $meridiem_entry; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name">Duration of Waiting</label>
                    <input type="number" class="form-control highlight-input" name="waiting_duration" id="waiting_duration" aria-describedby="name_help" maxlength="25" required>
                    <div class="invalid-feedback">This field is required</div>
                    <small id="name_help" class="form-text text-muted">Duration of waiting in minutes</small>
                </div>
                <div class="form-group">
                    <input type="submit" id="add_purpose" class="btn btn-primary col-md-6" value="Submit">
                </div>
            </form>
        </div>
        <div class="col-md-2"></div>
    </div>
</div>
