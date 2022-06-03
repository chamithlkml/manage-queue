<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: chamith
 * Date: 21/9/18
 * Time: 4:46 AM
 */
?>
<br>
<h4 class="text-center">Add Officers</h4>
<p class="lead text-center">Create officers to manage queue operations</p>
<div class="container">
    <div class="row">
        <div class="col-md-2">
        </div>
        <div id="alert-box" class="col-sm-8"></div>
        <div class="col-md-2">
        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
        </div>
        <div class="col-md-8">
            <form id="add_officers_form" class="add_officers_form" novalidate>
                <input type="hidden" id="csrf_value" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control highlight-input" id="name" name="name" aria-describedby="name_help" placeholder="Cindy" minlength="2" maxlength="40" required>
                    <div class="invalid-feedback">Please enter a valid name</div>
                </div>
                <div class="form-group">
                    <label for="mobile_number">Mobile Number</label>
                    <input type="tel" class="form-control highlight-input" id="mobile_number" name="mobile_number" aria-describedby="mobile_number_help" placeholder="07xxxxxxxx" onkeypress="return isNumberKey(event)" minlength="10" maxlength="10" type="text" required>
                    <div class="invalid-feedback">Need a valid mobile number</div>
                    <small id="mobile_number_help" class="form-text text-muted">We'll verify the mobile number and then send you the queue number</small>
                </div>
                <div class="form-group">
                    <label for="exampleFormControlSelect1">Role</label>
                    <select class="form-control" id="role">
                        <?php foreach($officer_roles as $role_id => $role_name){ ?>
                        <option value="<?php echo $role_id; ?>"><?php echo $role_name; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" id="add_officer_verification" class="btn btn-primary col-md-6" value="Submit">
                </div>
            </form>
        </div>
        <div class="col-md-2"></div>
    </div>
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <form id="verify_officer_form" class="verify_number keep-hidden" novalidate>
                <input type="hidden" id="verify_number_csrf_value" name="<?=$csrf['name'];?>" value="" />
                <input type="hidden" id="officers_id" name="officers_id" value="" />
                <div class="form-group">
                    <label for="verification_code"></label>
                    <input class="form-control highlight-input" name="verification_code" onkeypress="return isNumberKey(event)" maxlength="6" id="verification_code" required>
                    <div class="invalid-feedback">Please enter the verification code you received on your phone here.</div>
                </div>
                <div class="form-group">
                    <input type="submit" name="verify_mobile_no" id="verify_mobile_no" class="btn btn-primary col-md-6" value="Verify">
                </div>
            </form>
            <form id="resend_verification_code" class="resend_verification_code keep-hidden" novalidate>
                <input type="hidden" id="rvc_csrf_value" name="<?=$csrf['name'];?>" value="" />
                <input type="hidden" id="rvc_officers_id" name="officers_id" value="" />
                <div class="form-group">
                    <input type="submit" id="resend_vc" class="btn btn-primary col-md-6" value="Resend Verification Code">
                </div>
            </form>
            <form id="apply_token_btn" class="apply_token keep-hidden" novalidate>
                <div class="form-group">
                    <a href="/" class="btn btn-primary active" role="button" aria-pressed="true">Get a new token</a>
                </div>
            </form>
        </div>
        <div class="col-md-2"></div>
    </div>
</div>
