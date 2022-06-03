<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<br>
<h4 class="text-center"><?php echo $panel_name ?></h4>
<p class="lead text-center">Add CR</p>
<div class="container">
    <div class="row">
        <div class="col-md-2">
        </div>
        <div class="col-md-8">
            <form id="add_cr_form" method="post" class="officer_add_cr" novalidate>
                <input type="hidden" id="add_cr_csrf_value" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                <div class="form-group">
                    <label for="mobile_number">Vehicle No</label>
                    <input type="text" class="form-control highlight-input" name="vehicle_no" id="vehicle_no" aria-describedby="vehicle_no_help" minlength="2" maxlength="10" required>
                    <div class="invalid-feedback">Need a valid vehicle no</div>
                    <small id="vehicle_no_help" class="form-text text-muted">For reference</small>
                </div>
                <div class="form-group">
                    <label for="cr">CR</label>
                    <input type="file" class="form-control" name="cr" id="cr" required>
                    <div class="invalid-feedback">Need a CR in PDF format</div>
                </div>
                <div class="form-group">
                    <input type="submit" id="add_cr" class="btn btn-primary col-md-6" value="Add">
                </div>
            </form>
        </div>
        <div class="col-md-2"></div>
    </div>
</div>
