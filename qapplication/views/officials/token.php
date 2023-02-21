<?php
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 9/24/18
 * Time: 3:10 PM
 */
?>
<br>
<h4 class="text-center"><?php echo $panel_name; ?>: Token Details</h4>
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
                        <td><?php echo $token->name; ?></td>
                        <td><?php echo $token->date_to_office; ?></td>
                        <td><?php echo $token->queue_no; ?></td>
                        <td><?php echo $token->expected_service_on; ?></td>
                        <td><?php echo $token->status; ?></td>
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
            <form method="post" action="/official/reach_office" id="get_token_form" class="get_token" novalidate>
                <input type="hidden" id="get_token_csrf_value" name="<?=$csrf['name'];?>" value="<?=$csrf['hash'];?>" />
                <input type="hidden" id="tokens_id" name="tokens_id" value="<?php echo $token->id; ?>" />
                <?php if($show_mark_arrival_button){ ?>
                    <div class="form-group">
                        <input type="submit" id="send_verification_code" class="btn btn-primary col-md-6" value="Mark as arrived to the office">
                    </div>
                <?php } ?>
            </form>
        </div>
        <div class="col-md-1"></div>
    </div>
    <div class="row">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <a href="/official/index">
                <button type="button" class="btn btn-secondary col-md-6">Back to dashboard</button>
            </a>
        </div>
        <div class="col-md-1"></div>
    </div>
</div>
