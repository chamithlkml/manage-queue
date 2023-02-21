<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 8/22/18
 * Time: 1:58 PM
 */
class Token extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index()
    {
        try{

            if($this->input->method() != 'post')
            {
                throw new Exception("Method not supported");
            }

            if(strlen($this->input->post('mobile_number')) != 10)
            {
                throw new Exception("Mobile number must be 10 digits");
            }

            if(strlen($this->input->post('name')) == 0)
            {
                throw new Exception("Name is required.");
            }

            if(strlen($this->input->post('name')) > 30 )
            {
                throw new Exception("Name length must not exceed 30");
            }

            if(strlen($this->input->post('date_to_office')) == 0)
            {
                throw new Exception("Date to the office is required");
            }

            if(strlen($this->input->post('purpose_id')) == 0)
                throw new Exception("Purpose is required");

            $this->load->model('purposes');

            $purpose = $this->purposes->get_purpose($this->input->post('purpose_id'));

            if( ! $purpose)
                throw new Exception("Purpose is not valid");

            $this->config->load('appconfig', TRUE);
            $this->load->library('Utilities');

            $this->load->library(
                'Textit',
                array(
                    'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                    'username' => $this->config->item('textit_username', 'appconfig'),
                    'password' => $this->config->item('textit_password', 'appconfig')
                )
            );

            $this->load->model('tokens');
            $this->load->model('officers');

            log_message('debug', "start_token");

            if($this->session->has_userdata('managequeue_officer'))
            {

                $officer = $this->officers->get_officer($this->session->userdata('managequeue_officer'));

                if( ! $officer)
                {
                    $this->session->unset_userdata('managequeue_officer');
                    throw new Exception("Officer: " . $this->session->userdata('managequeue_officer') . " not found");
                }

                if($officer->role == 'receptionist' && $this->config->item('send_token_without_verification_by_receptionist', 'appconfig'))
                {
                    $tokens_id = $this->tokens->add_new_token(
                        $this->input->post('name'),
                        $this->input->post('mobile_number'),
                        $this->input->post('date_to_office'),
                        null, #mobile_number_verification_code
                        null, #officer_id
                        $purpose->id
                    );

                    if(!$tokens_id)
                    {
                        throw new Exception("Failed to store new token details as a verified mobile number by receptionist");
                    }

                    $new_token = $this->tokens->issue_queue_position(
                        $tokens_id,
                        $this->config->item('service_length', 'appconfig'),
                        $this->config->item('office_opens_at', 'appconfig'),
                        $this->config->item('office_closes_at', 'appconfig'),
                        $this->config->item('max_queue_tickets_count', 'appconfig'),
                        $this->config->item('service_breaks', 'appconfig'),
                        $this->config->item('special_service_breaks', 'appconfig'),
                        $this->config->item('token_uuid_length', 'appconfig')
                    );

                    $message = "Hi " . $new_token->name . ", your token number is " . $new_token->queue_no . ". Expected service time: " . $new_token->expected_service_on . ". Token Reference ID: " . $new_token->uuid;

                    if($this->textit->send_message($new_token->mobile_number, $message))
                    {
                        echo json_encode(
                            array(
                                'status' => true,
                                'message' => $message,
                                'queue_no_issued' => true,
                                'csrf_name' => $this->security->get_csrf_token_name(),
                                'csrf_hash' => $this->security->get_csrf_hash(),
                                'tokens_id' => $new_token->id
                            )
                        );
                    }

                    return;

                }

            }

            $existing_tokens_count = $this->tokens->get_existing_tokens_count(
                $this->input->post('mobile_number'),
                $this->input->post('date_to_office')
            );

            if($existing_tokens_count > 0)
            {
                if( ! $this->utilities->is_from_valid_agent($this->input->post('mobile_number'), $existing_tokens_count))
                {
                    throw new Exception("Invalid mobile number provided. Please check whether you have exceeded allowed 
                    agent tokens limit or try with a different mobile number");
                }

                $tokens_id = $this->tokens->add_new_token(
                    $this->input->post('name'),
                    $this->input->post('mobile_number'),
                    $this->input->post('date_to_office')
                );

                if(!$tokens_id)
                {
                    throw new Exception("Failed to store new token details as a verified mobile number");
                }

                $new_token = $this->tokens->issue_queue_position(
                    $tokens_id,
                    $this->config->item('service_length', 'appconfig'),
                    $this->config->item('office_opens_at', 'appconfig'),
                    $this->config->item('office_closes_at', 'appconfig'),
                    $this->config->item('max_queue_tickets_count', 'appconfig'),
                    $this->config->item('service_breaks', 'appconfig'),
                    $this->config->item('special_service_breaks', 'appconfig'),
                    $this->config->item('token_uuid_length', 'appconfig')
                );

                $message = "Hi " . $new_token->name . ", your token number is " . $new_token->queue_no . ". Expected service time: " . $new_token->expected_service_on . ". Token Reference ID: " . $new_token->uuid;

                if($this->textit->send_message($new_token->mobile_number, $message))
                {
                    echo json_encode(
                        array(
                            'status' => true,
                            'message' => $message,
                            'queue_no_issued' => true
                        )
                    );
                }

                return;
            }

            $random_code = $this->utilities->generate_random_number();

            log_message('debug', 'random_code: ' . $random_code);

            $tokens_id = $this->tokens->add_new_token(
                $this->input->post('name'),
                $this->input->post('mobile_number'),
                $this->input->post('date_to_office'),
                $random_code
            );

            if(!$tokens_id)
            {
                throw new Exception("Failed to store new token details");
            }

            if($tokens_id)
            {

                $message = "Please enter " . $random_code . " to verify your mobile number at ManageQ.";

                if($this->textit->send_message($this->input->post('mobile_number'), $message))
                {
                    echo json_encode(
                        array(
                            'status' => true,
                            'tokens_id' => $tokens_id,
                            'message' => 'Mobile verification code has been sent to the number. Please enter it to here and click on `Verify Mobile Number`',
                            'csrf_name' => $this->security->get_csrf_token_name(),
                            'csrf_hash' => $this->security->get_csrf_hash(),
                            'queue_no_issued' => false
                        )
                    );
                }

            }

            return;

        }catch (Exception $e){
            log_message('error', $e->getMessage());

            echo json_encode(
                array(
                    'status' => false,
                    'message' => $e->getMessage(),
                    'csrf_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash' => $this->security->get_csrf_hash()
                )
            );
        }
    }


    public function verify_mobile()
    {

        try{
            if($this->input->method() != 'post')
            {
                throw new Exception("Method not supported");
            }

            $this->config->load('appconfig', TRUE);
            $this->load->model('tokens');

            if(!$this->tokens->verify_mobile_number($this->input->post('tokens_id'), $this->input->post('verification_code')))
            {
                throw new Exception("Wrong verification code. Please try again.");
            }

            $new_token = $this->tokens->issue_queue_position(
                $this->input->post('tokens_id'),
                $this->config->item('service_length', 'appconfig'),
                $this->config->item('office_opens_at', 'appconfig'),
                $this->config->item('office_closes_at', 'appconfig'),
                $this->config->item('max_queue_tickets_count', 'appconfig'),
                $this->config->item('service_breaks', 'appconfig'),
                $this->config->item('special_service_breaks', 'appconfig'),
                $this->config->item('token_uuid_length', 'appconfig')
            );

            $this->load->library(
                'Textit',
                array(
                    'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                    'username' => $this->config->item('textit_username', 'appconfig'),
                    'password' => $this->config->item('textit_password', 'appconfig')
                )
            );

            $message = "Hi " . $new_token->name . ", your token number is " . $new_token->queue_no . ". Expected service time: " . $new_token->expected_service_on . ". Token Reference ID: " . $new_token->uuid;

            if($this->textit->send_message($new_token->mobile_number, $message))
            {
                echo json_encode(
                    array(
                        'status' => true,
                        'message' => $message
                    )
                );
            }

            return;

        }catch(Exception $e){
            log_message('error', $e->getMessage());

            echo json_encode(
                array(
                    'status' => false,
                    'message' => $e->getMessage(),
                    'csrf_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash' => $this->security->get_csrf_hash()
                )
            );
        }

    }

    public function resend_verification_code()
    {
        try{

            if($this->input->method() != 'post')
            {
                throw new Exception("Method not supported");
            }

            $this->config->load('appconfig', TRUE);
            $this->load->model('tokens');
            $this->load->library('Utilities');
            $this->load->library(
                'Textit',
                array(
                    'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                    'username' => $this->config->item('textit_username', 'appconfig'),
                    'password' => $this->config->item('textit_password', 'appconfig')
                )
            );

            $tokens_id = $this->input->post('tokens_id');
            $token = $this->tokens->get_queue($tokens_id);

            $random_code = $this->utilities->generate_random_number();

            $this->tokens->re_issue_verification_code($tokens_id, $random_code);

            $message = "Please enter " . $random_code . " to verify your mobile number at ManageQ.";

            if($this->textit->send_message($token->mobile_number, $message))
            {
                echo json_encode(
                    array(
                        'status' => true,
                        'tokens_id' => $tokens_id,
                        'message' => 'Mobile verification code has been resent to the number. Please enter it here and click on `Verify Mobile Number`',
                        'csrf_name' => $this->security->get_csrf_token_name(),
                        'csrf_hash' => $this->security->get_csrf_hash()
                    )
                );
            }

            return;

        }catch (Exception $e){
            log_message('error', $e->getMessage());

            echo json_encode(
                array(
                    'status' => false,
                    'message' => $e->getMessage(),
                    'csrf_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash' => $this->security->get_csrf_hash()
                )
            );
        }
    }


    public function next_token_info()
    {
        try
        {
            if($this->input->method() != 'post')
                throw new Exception("Method not supported");

            $this->config->load('appconfig', TRUE);
            $this->load->model('tokens');
            $this->load->library('Utilities');
            $this->load->library(
                'Textit',
                array(
                    'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                    'username' => $this->config->item('textit_username', 'appconfig'),
                    'password' => $this->config->item('textit_password', 'appconfig')
                )
            );

            if(is_null($this->input->post('date_to_office')))
                throw new Exception("Date to office is required.");

            if(is_null($this->input->post('officer_id')))
                throw new Exception("Date to office is required.");

            $this->load->model('officers');

            $officer = $this->officers->get_officer($this->input->post('officer_id'));

            if( ! $officer)
                throw new Exception("Officer not found");

            $last_job_started_token = $this->tokens->get_last_job_started_token(
                $this->input->post('date_to_office'),
                $this->input->post('officer_id')
            );

            $on_going_queue_no = (is_null($last_job_started_token)) ? 0 : $last_job_started_token->queue_no;
            $last_token_expected_service_time = $this->tokens->get_last_token_expected_service_time(
                $officer->id,
                $this->input->post('date_to_office'),
                $this->config->item('office_opens_at', 'appconfig')
            );

            echo json_encode(array(
                'status' => true,
                'on_going_queue_no' => $on_going_queue_no,
                'last_token_expected_service_time' => $last_token_expected_service_time,
                'csrf_name' => $this->security->get_csrf_token_name(),
                'csrf_hash' => $this->security->get_csrf_hash()
            ));


        }catch (Exception $e){
            log_message('error', $e->getMessage());

            echo json_encode(
                array(
                    'status' => false,
                    'message' => $e->getMessage(),
                    'csrf_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash' => $this->security->get_csrf_hash()
                )
            );
        }
    }

}
