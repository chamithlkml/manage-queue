<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');



//include Rest Controller library


require APPPATH . 'libraries/REST_Controller.php';

/**
 * Created by ManageQ Pvt Ltd
 * User: chamith
 * Date: 10/22/18
 * Time: 5:27 AM
 */

class Sms extends \Restserver\Libraries\REST_Controller
{
    public function __construct() {
        parent::__construct();

        $this->load->helper('url');
        $this->config->load('appconfig', TRUE);
    }

    public function token_get()
    {
        try{

            $sms_data = $_SERVER['QUERY_STRING'];

            parse_str($sms_data, $data_array);

            log_message('debug', 'get_array: ' . print_r($data_array, true));

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

            log_message('debug', 'libraries have been loaded');

            $customer_name = trim($data_array['Body']) != "" ? $data_array['Body'] : "ManageQ User";
            $mobile_number = $this->utilities->get_srilankan_standardized_phone_number($data_array['From']);

            log_message('debug', 'mobile_number: ' . $mobile_number);

            $date_to_office = date("Y-m-d");

            $tokens_id = $this->tokens->add_new_token(
                $customer_name,
                $mobile_number,
                $date_to_office,
                null,
                null,
                $this->config->item('other_purpose_id', 'appconfig')
            );

            if(!$tokens_id)
            {
                throw new Exception("Failed to store new token details as a verified mobile number by SMS");
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

            $message = "Hi " . $customer_name . ", your token number is " . $new_token->queue_no . ". Expected service time: " . $new_token->expected_service_on . ". Token Reference ID: " . $new_token->uuid;

            $token_sent_result = $this->textit->send_message($new_token->mobile_number, $message);

            if(! $token_sent_result)
                throw new Exception("Failed to send token message to " . $new_token->mobile_number);


            if($this->config->item('mark_arrival_with_token', 'appconfig'))
            {

                $token = $this->tokens->get_queue($tokens_id);

                if( ! $token)
                    throw new Exception("Token not found at message");


                $last_job_started_token = $this->tokens->get_last_job_started_token(
                    $token->date_to_office,
                    null,
                    $token->purpose_id
                );

                log_message('debug', '$last_job_started_token: ' . print_r($last_job_started_token, true));

                #mili seconds
                $estimated_waiting_time_seconds = $this->config->item('reception_to_office_walk_time', 'appconfig') * 60;

                if($last_job_started_token)
                {
                    $expected_last_job_done_timestamp = strtotime($last_job_started_token->job_started_on) + ( $this->config->item('service_length', 'appconfig')*60 );
                    $now_timestamp = time();

                    $estimated_waiting_time_seconds =  $expected_last_job_done_timestamp - $now_timestamp + ($this->config->item('reception_to_office_walk_time', 'appconfig')*60);
                }else
                {
                    $last_office_arrived_token = $this->tokens->get_last_office_arrived_token($token->purpose_id);

                    if($last_office_arrived_token)
                    {
                        $last_office_arrived_token_estimated_waiting_time_seconds = $last_office_arrived_token->estimated_waiting_time * 60;
                        $estimated_waiting_time_seconds = $last_office_arrived_token_estimated_waiting_time_seconds + ( $this->config->item('service_length', 'appconfig')*60 ) + ($this->config->item('reception_to_office_walk_time', 'appconfig')*60);
                    }


                }

                $this->tokens->set_arrival_to_office($token->id, $estimated_waiting_time_seconds);

                $this->load->library(
                    'Textit',
                    array(
                        'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                        'username' => $this->config->item('textit_username', 'appconfig'),
                        'password' => $this->config->item('textit_password', 'appconfig')
                    )
                );

                $this->load->library('Utilities');

                $message = "Hi " . $token->name . ", your estimated waiting time is " . $this->utilities->get_time_minutes_seconds_format($estimated_waiting_time_seconds);

                $sent_mark_arrival_message = $this->textit->send_message($token->mobile_number, $message);

                if(!$sent_mark_arrival_message)
                    throw new Exception("Failed to deliver mark arrival message at read message");

                echo json_encode(
                    array(
                        'status' => true,
                        'message' => "Queue token issued and marked as arrived to the office"
                    )
                );

            }else
                {
                    echo json_encode(
                        array(
                            'status' => true,
                            'message' => "Queue token issued"
                        )
                    );
                }


            return true;

        }catch (Exception $e){
            log_message('error', "Error occured at token_get: " . $e->getMessage());

            echo json_encode(
                array(
                    'status' => false,
                    'message' => $e->getMessage()
                )
            );
        }
    }

}