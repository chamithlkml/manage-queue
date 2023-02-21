<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 14/9/18
 * Time: 4:39 AM
 */

class Official extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->config->load('appconfig', TRUE);
        $this->load->model('officers');
    }

    private function authenticate_officer()
    {
        if( ! $this->session->has_userdata('managequeue_officer'))
        {
            redirect('/official/login', 'refresh');
            return;
        }

        $officer = $this->officers->get_officer($this->session->userdata('managequeue_officer'));

        if( ! $officer)
            redirect('/official/login', 'refresh');

        return $officer;
    }

    public function index()
    {
        try
        {
            if($this->input->method() === 'get')
            {
                $officer = $this->authenticate_officer();

                if( ! $officer)
                    redirect('/official/login', 'refresh');

                $show_select_date = $officer->role == 'admin_receptionist';

                $this->load->model('tokens');

                $next_service_token = null;
                $service_started_token = null;

                if($officer->role == 'service_officer')
                {

                    $service_started_token = $this->tokens->get_service_started_token($officer->id);

                    if($service_started_token)
                    {
                        $service_started_token->status = $this->tokens->get_status($service_started_token->id);

                    }else
                        {
                            $next_service_token = $this->tokens->get_next_service_token();

                            if($next_service_token)
                                $next_service_token->status = $this->tokens->get_status($next_service_token->id);
                        }

                }

                $show_search_token_form = $officer->role == 'receptionist' || $officer->role == 'admin_receptionist';

                $officer_role_names = $this->config->item('officer_roles', 'appconfig');

                $panel_name = $officer_role_names[$officer->role] . " Panel";

                $show_cr_officer_buttons = false;

                if($officer->role == 'cr_officer')
                {
                    $show_cr_officer_buttons = true;
                }

                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'content' => 'officials/index',
                    'scripts' => array('js/bootstrap-datepicker.min.js', 'js/scripts/officials/index.js?t=' . time()),
                    'officer' => $officer,
                    'show_select_date' => $show_select_date,
                    'next_service_token' => $next_service_token,
                    'show_search_token_form' => $show_search_token_form,
                    'service_started_token' => $service_started_token,
                    'panel_name' => $panel_name,
                    'show_button_to_dashboard' => $officer->role == 'receptionist',
                    'show_cr_officer_buttons' => $show_cr_officer_buttons
                );
                $this->load->view('layout', $data);
            }
            else
            {
                throw new Exception("method not supported.");
            }
        }
        catch (Exception $e)
        {
            log_message('debug', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }

    }

    public function login()
    {
        try{
            $this->session->unset_userdata('managequeue_officer');

            if($this->input->method() === 'get')
            {
                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'content' => 'officials/login',
                    'scripts' => array('js/scripts/officials/login.js?t=' . time()),
                    'officer_roles' => $this->config->item('officer_roles', 'appconfig')
                );
                $this->load->view('layout', $data);
            }
            else if($this->input->method() === 'post')
            {

                try{

                    $officer = $this->officers->get_officer(
                        $this->input->post('officers_id'),
                        $this->input->post('mobile_number'),
                        $this->input->post('role'),
                        true,
                        true
                    );

                    if( ! $officer)
                        throw new Exception("Officer not found.");

                    $this->load->library('Utilities');

                    $random_code = $this->utilities->generate_random_number(4);

                    $this->officers->set_mobile_number_verification_code(
                        $officer->id,
                        $random_code
                    );

                    $this->load->library(
                        'Textit',
                        array(
                            'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                            'username' => $this->config->item('textit_username', 'appconfig'),
                            'password' => $this->config->item('textit_password', 'appconfig')
                        )
                    );

                    $message = "Hi " . $officer->name . ", please enter " . $random_code . " and click `Login`";

                    if($this->textit->send_message($officer->mobile_number, $message))
                    {
                        echo json_encode(
                            array(
                                'status' => true,
                                'officers_id' => $officer->id,
                                'message' => 'Officer verification code has been sent to your mobile number. 
                                Please enter it and click `Login`',
                                'csrf_name' => $this->security->get_csrf_token_name(),
                                'csrf_hash' => $this->security->get_csrf_hash(),
                            )
                        );
                    }

                }
                catch(Exception $e)
                {
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
        catch (Exception $e)
        {
            log_message('debug', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }

    }


    public function verify()
    {
        try{
            if($this->input->method() === 'post')
            {
                try{

                    if( ! $this->officers->verify_mobile_number(
                            $this->input->post('officers_id'),
                            $this->input->post('verification_code')
                        )
                    )
                    {
                        throw new Exception("Wrong verification code. Please try again.");
                    }


                    $officer = $this->officers->get_officer(
                        $this->input->post('officers_id'),
                        null,
                        null,
                        1,
                        true
                    );

                    if(!$officer)
                    {
                        $this->session->unset_userdata('managequeue_officer');

                        throw new Exception("Officer not found.");
                    }

                    $this->session->set_userdata(
                        array(
                            'managequeue_officer' => $officer->id
                        )
                    );

                    echo json_encode(array(
                        'status' => true,
                        'login' => true,
                        'message' => "Successfully logged in"
                    ));

                }
                catch(Exception $e)
                {
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
            else
                {
                    throw new Exception("Method not supported");
                }
        }
        catch (Exception $e)
        {
            log_message('debug', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('managequeue_officer');

        redirect('/official/login', 'refresh');
    }

    public function search_token()
    {
        try
        {
            $officer = $this->authenticate_officer();


            try{

                if($this->input->method() !== 'post')
                {
                    throw new Exception("Method not supported");
                }

                if(! $officer)
                    throw new Exception("Officer not found");

                if($officer->role == "admin_receptionist")
                {
                    if(is_null($this->input->post('date_to_office')))
                        throw new Exception("Date to office is required");

                    if(is_null($this->input->post('token_uuid')))
                        throw new Exception("Token Reference ID is required");
                }

                if($officer->role == "receptionist")
                {
                    if(is_null($this->input->post('token_uuid')))
                        throw new Exception("Token Reference ID is required");
                }

                $this->load->model('tokens');

                if( ! $officer)
                    throw new Exception("Officer not found");

                $today = new DateTime();

                $date_to_office = $officer->role == "receptionist" ? $today->format('Y-m-d') : $this->input->post('date_to_office');

                #token_uuid
                $token = $this->tokens->get_queue(
                    null,
                    null,
                    $date_to_office,
                    $this->input->post('token_uuid')
                );

                if( ! $token)
                    throw new Exception("Token not found");

                $this->session->set_flashdata('valid_token', $token->id);

                redirect('/official/tokens/' . $token->id);

            }
            catch(Exception $e)
            {
                $this->session->set_flashdata('error_message', $e->getMessage());
                redirect('/official/index', 'refresh');
            }
        }
        catch (Exception $e)
        {
            log_message('error', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    public function tokens($token_id)
    {
        try
        {

            $this->load->model('tokens');

            $officer = $this->authenticate_officer();

            if( ! $officer)
                throw new Exception("Officer not found");

            if(is_null($token_id))
                throw new Exception("Token ID not found");

            $token = $this->tokens->get_queue($token_id);

            if( ! $token)
                throw new Exception("Token not found");

            $officer_role_names = $this->config->item('officer_roles', 'appconfig');

            $panel_name = $officer_role_names[$officer->role] . " Panel";

            $today = new DateTime();
            $date_to_office = DateTime::createFromFormat('Y-m-d' , $token->date_to_office);

            $show_mark_arrival_button = is_null($token->arrived_to_office_on) && $officer->role == "receptionist" && $today->diff($date_to_office)->days == 0;

            $token->status = $this->tokens->get_status($token_id);

            $data = array(
                'csrf' => array(
                    'name' => $this->security->get_csrf_token_name(),
                    'hash' => $this->security->get_csrf_hash()
                ),
                'content' => 'officials/token',
                'scripts' => array( 'js/scripts/officials/token.js?t=' . time()),
                'officer' => $officer,
                'token' => $token,
                'show_mark_arrival_button' => $show_mark_arrival_button,
                'panel_name' => $panel_name
            );

            $this->load->view('layout', $data);

        }
        catch (Exception $e)
        {
            log_message('error', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    public function reach_office()
    {
        try{

            if($this->input->method() !== 'post')
                throw new Exception("Method not supported");

            $officer = $this->authenticate_officer();

            if( ! $officer)
                throw new Exception("Officer not found");

            if(is_null($this->input->post('tokens_id')))
                throw new Exception("Token ID is required");

            $this->load->model('tokens');
            $this->load->model('waitingPoints');

            $today = date("Y-m-d");

            $token = $this->tokens->get_queue($this->input->post('tokens_id'), null, $today);

            if( ! $token)
                throw new Exception("Token not found");

            $last_job_started_token = $this->tokens->get_last_job_started_token($token->date_to_office, null, $token->purpose_id);

            log_message('debug', '$last_job_started_token: ' . print_r($last_job_started_token, true));

            #seconds
            $estimated_waiting_time_seconds = $this->config->item('reception_to_office_walk_time', 'appconfig') * 60;

            $waiting_points_for_this_purpose = $this->waitingPoints->get_waiting_points($token->purpose_id, $today);

            $now = time();

            if($waiting_points_for_this_purpose && (count($waiting_points_for_this_purpose) > 0))
            {
                foreach($waiting_points_for_this_purpose as $waiting_point_for_this_purpose)
                {
                    $waiting_point_timestamp = strtotime($waiting_point_for_this_purpose->waiting_point_on);

                    if($waiting_point_timestamp > $now)
                    {
                        $estimated_waiting_time_seconds = $estimated_waiting_time_seconds + ($waiting_point_timestamp - $now) + $waiting_point_for_this_purpose->duration*60;
                        break;
                    }
                }
            }

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

            $this->textit->send_message($token->mobile_number, $message);

            $this->session->set_flashdata('success_message', "Token marked as arrived to office");

            $this->session->set_flashdata('valid_token', $token->id);

            redirect('/official/tokens/' . $token->id, 'refresh');

        }
        catch (Exception $e)
        {
            log_message('error', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    public function service_started()
    {
        try{
            if($this->input->method() !== 'post')
                throw new Exception("Method not supported");

            $officer = $this->authenticate_officer();

            if( ! $officer)
                throw new Exception("Officer not found");

            if(is_null($this->input->post('tokens_id')))
                throw new Exception("Token ID is required");

            $this->load->model('tokens');

            $token = $this->tokens->get_queue($this->input->post('tokens_id'));

            if( ! $token)
                throw new Exception("Token not found");

            $this->tokens->set_service_started($token->id, $officer->id);

            $this->load->library(
                'Textit',
                array(
                    'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                    'username' => $this->config->item('textit_username', 'appconfig'),
                    'password' => $this->config->item('textit_password', 'appconfig')
                )
            );

            $message = "Hi " . $token->name . ", you may now see the " . $this->config->item('officer_title', 'appconfig') . ", " . $officer->name;

            $this->textit->send_message($token->mobile_number, $message);

            $this->session->set_flashdata('success_message', "Token marked as service started");

            $this->session->set_flashdata('valid_token', $token->id);

            redirect('/official/tokens/' . $token->id, 'refresh');

        }
        catch (Exception $e)
        {
            log_message('error', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    public function service_given()
    {
        try{
            if($this->input->method() !== 'post')
                throw new Exception("Method not supported");

            $officer = $this->authenticate_officer();

            if( ! $officer)
                throw new Exception("Officer not found");

            if(is_null($this->input->post('tokens_id')))
                throw new Exception("Token ID is required");

            $this->load->model('tokens');

            $token = $this->tokens->get_queue($this->input->post('tokens_id'));

            if( ! $token)
                throw new Exception("Token not found");


            $this->tokens->set_service_given($token->id);

            $this->session->set_flashdata('success_message', "Token marked as service given");

            $this->session->set_flashdata('valid_token', $token->id);

            redirect('/official/tokens/' . $token->id, 'refresh');

        }
        catch (Exception $e)
        {
            log_message('error', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    public function add_cr()
    {
        try{
            if($this->input->method() == 'get')
            {
                $officer = $this->authenticate_officer();

                if( ! $officer)
                    throw new Exception("Officer not found");

                $officer_role_names = $this->config->item('officer_roles', 'appconfig');

                $panel_name = $officer_role_names[$officer->role] . " Panel";

                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'content' => 'officials/add_cr',
                    'scripts' => array( 'js/scripts/officials/add_cr.js?t=' . time()),
                    'officer' => $officer,
                    'panel_name' => $panel_name
                );

                $this->load->view('layout', $data);
            }else if($this->input->method() == 'post')
            {
                $config = array();
                $config['upload_path']          = './uploads/';
                $config['allowed_types']        = 'pdf';

                $this->load->library('upload', $config);

                
            }



        }catch(Exception $e){
            log_message('error', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

}