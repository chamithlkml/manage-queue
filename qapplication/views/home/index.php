<?php
/**
 * Created by Dropsuite Pte Ltd.
 * User: chamith
 * Date: 8/12/18
 * Time: 11:27 PM
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<br>
<h4 class="text-center">Avoid queue</h4>
<p class="lead text-center">Get a token online to avoid queue at the office</p>
<div class="container">
    <div class="row">
        <div class="col-md-2">
        </div>
        <div id="alert-box" class="col-sm-8">
        </div>
        <div class="col-md-2">
        </div>
    </div>
    <div class="row">
        <div class="col-md-2">
        </div>
        <div class="col-md-8">
            <form id="get_token_form" class="get_token" novalidate>
                <input type="hidden" id="get_token_csrf_value" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                <div class="form-group">
                    <label for="name">Name</label>
                    <input class="form-control highlight-input" name="name" id="name" aria-describedby="name_help" placeholder="Andy" minlength="2" maxlength="25" required>
                    <div class="invalid-feedback">Need a valid name with 2-25 character length</div>
                    <small id="name_help" class="form-text text-muted">For reference only</small>
                </div>
                <div class="form-group">
                    <label for="mobile_number">Mobile Number</label>
                    <input type="tel" class="form-control highlight-input" id="mobile_number" name="mobile_number" aria-describedby="mobile_number_help" placeholder="07xxxxxxxx" onkeypress="return isNumberKey(event)" minlength="10" maxlength="10" type="text" required>
                    <div class="invalid-feedback">Need a valid mobile number</div>
                    <small id="mobile_number_help" class="form-text text-muted">We'll verify the mobile number and then send you the queue number</small>
                </div>
                <div class="form-group">
                    <label for="purpose">Purpose</label>
                    <select class="form-control highlight-input" id="purpose_id" name="purpose_id" aria-describedby="purpose_help" required>
                        <option value="">Select</option>
                        <?php foreach($purposes as $purpose){ ?>
                            <option value="<?php echo $purpose->id; ?>"><?php echo $purpose->name; ?></option>
                        <?php } ?>
                    </select>
                    <div class="invalid-feedback">Need a valid purpose</div>
                    <small id="purpose_help" class="form-text text-muted">If you are not sure about the purpose, please select "Other"</small>
                </div>
                <div class="form-group">
                    <label for="preferred_date">Date you wish to go to office</label>
                    <input type="text" class="form-control highlight-input" id="date_to_office" name="date_to_office" value="<?php echo date("Y-m-d"); ?>" required>
                    <div class="invalid-feedback">Please enter the date you wish to visit the office</div>
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-th"></span>
                    </div>
                </div>
                <div class="form-group">
                    <input type="submit" id="send_verification_code" class="btn btn-primary col-md-6" value="Submit">
                </div>
            </form>
        </div>
        <div class="col-md-2"></div>
    </div>
    <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <form id="verify_number_form" class="verify_number keep-hidden" novalidate>
                <input type="hidden" id="verify_number_csrf_value" name="<?=$csrf['name'];?>" value="" />
                <input type="hidden" id="tokens_id" name="tokens_id" value="" />
                <div class="form-group">
                    <label for="verification_code"></label>
                    <input class="form-control highlight-input" name="verification_code" onkeypress="return isNumberKey(event)" maxlength="4" id="verification_code" required>
                    <div class="invalid-feedback">Please enter the verification code you received on your phone here.</div>
                </div>
                <div class="form-group">
                    <input type="submit" name="verify_mobile_no" id="verify_mobile_no" class="btn btn-primary col-md-6" value="Verify Mobile Number">
                </div>
            </form>
            <form id="resend_verification_code" class="resend_verification_code keep-hidden" novalidate>
                <input type="hidden" id="rvc_csrf_value" name="<?=$csrf['name'];?>" value="" />
                <input type="hidden" id="rvc_tokens_id" name="tokens_id" value="" />
                <div class="form-group">
                    <input type="submit" id="resend_verification_code" class="btn btn-primary col-md-6" value="Resend Verification Code">
                </div>
            </form>
            <?php if(isset($officer)){ ?>
                <form method="post" action="/official/reach_office" id="reach_office_form" class="reach_office keep-hidden" novalidate>
                    <input type="hidden" id="reach_office_csrf_value" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                    <input type="hidden" id="reach_office_tokens_id" name="tokens_id" value="" />
                    <div class="form-group">
                        <input type="submit" id="reach_office" class="btn btn-primary col-md-6" value="Mark as arrived to the office">
                    </div>
                </form>
            <?php } ?>
            <form id="apply_token_btn" class="apply_token keep-hidden" novalidate>
                <div class="form-group">
                    <a href="/" class="btn btn-primary active col-md-6" role="button" aria-pressed="true">Get a new token</a>
                </div>
            </form>
        </div>
        <div class="col-md-2"></div>
    </div>
    <?php if(isset($officer)){ ?>
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <a href="/official/index">
                    <button type="button" class="btn btn-secondary col-md-6">Go to Dashboard</button>
                </a>
            </div>
            <div class="col-md-2"></div>
        </div>
    <?php } ?>
</div>
