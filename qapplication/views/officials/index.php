<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 9/15/18
 * Time: 10:30 AM
 */
?>
<br>
<h4 class="text-center"><?php echo $panel_name ?></h4>
<p class="lead text-center">Welcome <?php echo $officer->name; ?></p>
<div class="container">
    <div class="row">
        <div class="col-md-2"></div>
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
    <?php if(! is_null($next_service_token)){ ?>
        <div class="row">
            <div class="col-md-1">
            </div>
            <div id="alert-box" class="col-sm-10">
                <table class="table">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Date to office</th>
                        <th scope="col">Queue No</th>
                        <th scope="col">Expected Service Time</th>
                        <th scope="col">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?php echo $next_service_token->name; ?></td>
                        <td><?php echo $next_service_token->date_to_office; ?></td>
                        <td><?php echo $next_service_token->queue_no; ?></td>
                        <td><?php echo $next_service_token->expected_service_on; ?></td>
                        <td><?php echo $next_service_token->status; ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">
                <form method="post" action="/official/service_started" id="service_started_form" class="service_start" novalidate>
                    <input type="hidden" id="get_token_csrf_value" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                    <input type="hidden" id="tokens_id" name="tokens_id" value="<?php echo $next_service_token->id; ?>" />
                        <div class="form-group">
                            <input type="submit" id="send_verification_code" class="btn btn-primary col-md-6" value="Mark as service started">
                        </div>
                </form>
            </div>
            <div class="col-md-1"></div>
        </div>
    <?php } ?>
    <?php if(! is_null($service_started_token)){ ?>
        <div class="row">
            <div class="col-md-1">
            </div>
            <div id="alert-box" class="col-sm-10">
                <table class="table">
                    <thead class="thead-dark">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Date to office</th>
                        <th scope="col">Queue No</th>
                        <th scope="col">Expected Service Time</th>
                        <th scope="col">Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?php echo $service_started_token->name; ?></td>
                        <td><?php echo $service_started_token->date_to_office; ?></td>
                        <td><?php echo $service_started_token->queue_no; ?></td>
                        <td><?php echo $service_started_token->expected_service_on; ?></td>
                        <td><?php echo $service_started_token->status; ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-1">
            </div>
        </div>
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">
                <form method="post" action="/official/service_given" id="service_given_form" class="service_given" novalidate>
                    <input type="hidden" id="get_token_csrf_value" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                    <input type="hidden" id="tokens_id" name="tokens_id" value="<?php echo $service_started_token->id; ?>" />
                    <div class="form-group">
                        <input type="submit" id="send_verification_code" class="btn btn-primary col-md-6" value="Mark as service given">
                    </div>
                </form>
            </div>
            <div class="col-md-1"></div>
        </div>
    <?php } ?>
    <?php if($show_search_token_form){ ?>
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">
                <form method="post" action="/official/search_token" id="search_token_form" class="search_token_form" novalidate>
                    <input type="hidden" id="search_token_csrf_value" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                    <?php if($show_select_date){ ?>
                        <div class="form-group">
                            <label for="date_to_office">Date to Office</label>
                            <input type="tel" class="form-control highlight-input" id="date_to_office" name="date_to_office" aria-describedby="date_to_office_help" placeholder="" type="text" required>
                            <div class="invalid-feedback">Please select a date to office</div>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <label for="token_uuid">Token Reference ID</label>
                        <input type="text" class="form-control highlight-input" id="token_uuid" name="token_uuid" aria-describedby="token_uuid_help" placeholder="" minlength="1" maxlength="6" type="text" required>
                        <div class="invalid-feedback">Need a valid queue number</div>
                    </div>
                    <div class="form-group">
                        <input type="submit" id="search_token_button" class="btn btn-primary col-md-6" value="Submit">
                    </div>
                </form>
            </div>
            <div class="col-md-1"></div>
        </div>
    <?php } ?>
    <?php if($show_button_to_dashboard){ ?>
        <div class="row">
            <div class="col-md-1"></div>
            <div class="col-md-10">
                <a href="/">
                    <button type="button" class="btn btn-secondary col-md-6">Go to Token Issue Page</button>
                </a>
            </div>
            <div class="col-md-1"></div>
        </div>
    <?php } ?>
    <?php if($show_cr_officer_buttons){ ?>
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-1">
           <a href="/official/add_cr" >
               <button type="button" class="btn btn-primary">Add CR</button>
           </a>
        </div>
        <div class="col-md-1">
            <a href="/official/search_cr" >
                <button type="button" class="btn btn-primary">Search CR</button>
            </a>
        </div>
        <div class="col-md-1"></div>
    </div>
    <?php } ?>
</div>
