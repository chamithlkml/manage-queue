<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 9/15/18
 * Time: 10:40 AM
 */

class Admin extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->config->load('appconfig', TRUE);
    }

    public function index()
    {

        try
        {
            if($this->input->method() === 'get')
            {
                if( ! $this->session->has_userdata('managequeue_admin'))
                {
                    redirect('/admin/login', 'refresh');
                    return;
                }

                $this->load->model('officers');

                $admin = $this->officers->get_admin(null, $this->session->userdata('managequeue_admin'));

                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'content' => 'admin/index',
                    'scripts' => array( 'js/scripts/admin/index.js?t=' . time()),
                    'admin' => $admin
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
        $this->session->unset_userdata('managequeue_admin');

        try
        {
            if($this->input->method() === 'get')
            {

                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'content' => 'admin/login',
                    'scripts' => array('js/scripts/admin/login.js?t=' . time())
                );
                $this->load->view('layout', $data);
            }
            else if ($this->input->method() === 'post')
            {

                try
                {
                    log_message('debug', 'mobile_number: ' . $this->input->post('mobile_number'));

                    $this->load->model('officers');
                    $this->load->library('Utilities');

                    $this->load->library(
                        'Textit',
                        array(
                            'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                            'username' => $this->config->item('textit_username', 'appconfig'),
                            'password' => $this->config->item('textit_password', 'appconfig')
                        )
                    );

                    $admin = $this->officers->get_admin($this->input->post('mobile_number'), $this->input->post('officers_id'));

                    if(!$admin)
                    {
                        throw new Exception("Administrator not found. Please retry.");
                    }

                    $random_code = $this->utilities->generate_random_number(6);

                    $this->officers->issue_new_verification_token($admin->id, $random_code);

                    $message = "Hi " . $admin->name .", Please enter " . $random_code . ". to login into the admin panel.";

                    if($this->textit->send_message($admin->mobile_number, $message))
                    {
                        echo json_encode(
                            array(
                                'status' => true,
                                'officers_id' => $admin->id,
                                'message' => 'Admin verification code has been sent to your mobile number. Please enter it and click `Login`',
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
            else
            {
                throw new Exception("Method not supported");
            }

        }catch(Exception $e)
        {
            log_message('debug', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }

    }

    public function verify_mobile()
    {
        try
        {
            if ($this->input->method() === 'post')
            {

                try{
                    $this->load->model('officers');

                    if(!$this->officers->verify_mobile_number($this->input->post('officers_id'), $this->input->post('verification_code')))
                    {
                        throw new Exception("Wrong verification code. Please try again.");
                    }

                    $admin = $this->officers->get_officer($this->input->post('officers_id'));

                    if(!$admin)
                    {
                        $this->session->unset_userdata('managequeue_admin');

                        throw new Exception("Admin not found.");
                    }

                    $this->session->set_userdata(
                        array(
                            'managequeue_admin' => $admin->id
                        )
                    );

                    echo json_encode(array(
                        'status' => true,
                        'login' => true,
                        'message' => "Successfully logged in"
                    ));


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
            else
                {
                    throw new Exception("Method not supported");
                }
        }
        catch(Exception $e)
        {
            log_message('debug', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('managequeue_admin');

        redirect('/admin/login', 'refresh');
    }

    public function add_officers()
    {
        try
        {

            $this->verify_admin_login();

            $this->load->model('officers');

            if($this->input->method() === 'get')
            {

                $admin = $this->officers->get_admin(null, $this->session->userdata('managequeue_admin'));

                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'content' => 'admin/add_officers',
                    'scripts' => array( 'js/scripts/admin/add_officers.js?t=' . time()),
                    'admin' => $admin,
                    'officer_roles' => $this->config->item('officer_roles', 'appconfig')
                );

                $this->load->view('layout', $data);
            }
            else if($this->input->method() === 'post')
            {

                try
                {
                    $this->load->library('Utilities');
                    $this->load->model('officers');
                    $officer = null;
                    $mobile_verification_code = $this->utilities->generate_random_number(4);

                    if($this->input->post('officers_id'))
                    {
                        $officer = $this->officers->get_officer(
                            $this->input->post('officers_id')
                        );
                    }
                    else
                        {
                            $existing_officer = $this->officers->get_officer(
                                null,
                                $this->input->post('mobile_number'),
                                $this->input->post('role'),
                                true,
                                true
                            );

                            if($existing_officer)
                                throw new Exception("Officer exists already. Please try with a different mobile number");


                            $new_officer_id = $this->officers->add_new_officer(
                                $this->input->post('name'),
                                $this->input->post('mobile_number'),
                                $this->input->post('role'),
                                $mobile_verification_code
                            );

                            if(!$new_officer_id)
                                throw new Exception("Could not create the new officer. Please try again.");


                            $officer = $this->officers->get_officer($new_officer_id);
                        }

                    if(is_null($officer))
                        throw new Exception("Could not find the officer entry you tried to add");

                    $this->load->library(
                        'Textit',
                        array(
                            'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                            'username' => $this->config->item('textit_username', 'appconfig'),
                            'password' => $this->config->item('textit_password', 'appconfig')
                        )
                    );

                    $message = "Please enter " . $mobile_verification_code . " and click Verify.";

                    if($this->textit->send_message($officer->mobile_number, $message))
                    {
                        echo json_encode(
                            array(
                                'status' => true,
                                'officers_id' => $officer->id,
                                'message' => "Verification code has been sent to the officer's mobile number. Please enter it here and click `Login`",
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

    public function verify_officer()
    {
        try{
            if( ! $this->session->has_userdata('managequeue_admin'))
            {

                if($this->input->is_ajax_request())
                {
                    echo json_encode(
                        array(
                            'status' => false,
                            'message' => "Please Sign In"
                        )
                    );
                    return;
                }
                else
                {
                    redirect('/admin/login', 'refresh');
                    return;
                }

            }

            $this->load->model('officers');

            $admin = $this->officers->get_admin(null, $this->session->userdata('managequeue_admin'));

            if(! $admin)
                throw new Exception("Admin not found");


            $this->load->library(
                'Textit',
                array(
                    'api_url' => $this->config->item('textit_baseurl', 'appconfig'),
                    'username' => $this->config->item('textit_username', 'appconfig'),
                    'password' => $this->config->item('textit_password', 'appconfig')
                )
            );

            if(!$this->officers->verify_mobile_number($this->input->post('officers_id'), $this->input->post('verification_code')))
            {
                throw new Exception("Wrong verification code. Please try again.");
            }

            $officer = $this->officers->get_officer($this->input->post('officers_id'));

            if(!$officer)
                throw new Exception("Officer id not found.");

            $this->officers->set_creation_data(
                $this->input->post('officers_id'),
                date("Y-m-d H:i:s"),
                $admin->id
            );

            $officer_roles = $this->config->item('officer_roles', 'appconfig');

            $new_officer_role = $officer_roles[$officer->role];

            $message = "Congratulations " . $officer->name . "!, you are now registered as an officer with " . $this->config->item('app_name', 'appconfig');

            if($this->textit->send_message($officer->mobile_number, $message))
            {
                echo json_encode(
                    array(
                        'status' => true,
                        'message' => $officer->name . " was added as a " . $new_officer_role
                    )
                );
            }

        }catch(Exception $e)
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

    public function list_officers()
    {
        try
        {
            $admin = $this->verify_admin_login();

            $officers = $this->officers->get_officers();

            $this->load->Library('Utilities');

            foreach($officers as $officer)
            {
                $officer->is_deletable = $this->utilities->is_deletable_officer($officer);
            }

            $data = array(
                'content' => 'admin/list_officers',
                'styles' => array('css/dataTables.bootstrap4.min.css'),
                'scripts' => array('js/jquery.dataTables.min.js' , 'js/scripts/admin/list_officers.js'),
                'officers' => $officers,
                'admin' => $admin,
                'panel_name' => "Administrator Panel"
            );

            $this->load->view('layout', $data);

        }
        catch (Exception $e)
        {
            log_message('debug', $e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }

    }

    public function purposes()
    {
        try
        {
            $admin = $this->verify_admin_login();
            log_message('debug', "admin: " . print_r($admin, true));

            $this->load->model('purposes');
            $this->load->model('officers');

            if($this->input->method() === 'get')
            {

                $purposes = $this->purposes->get_active_purposes();

                foreach($purposes as $k=>$purpose)
                {
                    $purposes[$k]->created_by = ($purpose->created_by == 0) ? "Admin" : $this->officers->get_admin(null, $purpose->created_by)->name;
                }

                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'content' => 'admin/purposes',
                    'scripts' => array( 'js/scripts/admin/purposes.js?t=' . time()),
                    'admin' => $admin,
                    'purposes' => $purposes
                );

                $this->load->view('layout', $data);
            }


        }
        catch (Exception $e)
        {
            log_message('debug', 'Exception thrown at: add_purpose: '.$e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }

    }

    public function add_purpose()
    {

        try{

            $admin = $this->verify_admin_login();

            if($this->input->method() == 'get')
            {

                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'content' => 'admin/add_purpose',
                    'scripts' => array( 'js/scripts/admin/add_purpose.js?t=' . time()),
                    'admin' => $admin
                );

                $this->load->view('layout', $data);

            }
            else if($this->input->method() == 'post')
            {

                try{

                    if(is_null($this->input->post("purpose_name")))
                        throw new Exception("Purpose name is required");


                    $this->load->model('purposes');

                    $purpose_id = $this->purposes->add_new_purpose(
                        $this->input->post("purpose_name"),
                        $admin->id
                    );

                    if( ! $purpose_id)
                        throw new Exception("Failed to create new purpose");


                    $this->session->set_flashdata('success_message', "Purpose is added successfully");
                    redirect("/admin/purposes", 'refresh');

                }
                catch(Exception $e){
                    $this->session->set_flashdata('error_message', $e->getMessage());
                    redirect('/admin/add_purpose', 'refresh');
                }


            }
        }
        catch (Exception $e)
        {
            log_message('debug', 'Exception thrown at: add_purpose: '.$e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    public function waiting_points()
    {
        try
        {
            $admin = $this->verify_admin_login();

            if($this->input->method() == "get")
            {
                $this->load->model('waitingPoints');

                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'content' => 'admin/waiting_points',
                    'scripts' => array('js/jquery.dataTables.min.js', 'js/scripts/admin/waiting_points.js'),
                    'admin' => $admin,
                    'waiting_points' => $this->waitingPoints->get_active_waiting_points()
                );

                $this->load->view('layout', $data);

            }
            else if($this->input->method() == "post")
            {

            }
        }
        catch (Exception $e)
        {
            log_message('debug', 'Exception thrown at: waiting_points: '.$e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    public function add_waiting_point()
    {
        try
        {
            $admin = $this->verify_admin_login();

            if($this->input->method() == "get")
            {
                $this->load->model('purposes');

                $date_hour_minute = date('H:i:A');
                $date_time_chunks = explode(":", $date_hour_minute);
                $current_hour = $date_time_chunks[0];
                $current_meridiem = $date_time_chunks[2];

                $hour_entries = array();

                for($i = 0; $i <= 23;$i++)
                {
                    if($i <= 12)
                    {
                        if($i >= $current_hour)
                        {
                            $hour_entries[] = ($i < 10) ? "0" . $i : $i;
                        }
                    }else
                        {
                            if($i >= $current_hour)
                            {
                                $hour_entries[] = (($i-12) < 10) ?  "0" . ($i - 12) : ($i - 12);
                            }
                        }
                }

                $minute_entries = array();

                for($m=0; $m <60; $m=$m+5)
                {
                    $minute_entries[] = ($m == 0 || $m == 5) ? '0' . $m : $m;
                }

                $meridiem_entries = array();

                $meridiem_entries[] = "PM";

                if($current_meridiem == "AM")
                    array_unshift($meridiem_entries, "AM");

                $data = array(
                    'csrf' => array(
                        'name' => $this->security->get_csrf_token_name(),
                        'hash' => $this->security->get_csrf_hash()
                    ),
                    'styles' => array('css/jquery.timepicker.min.css'),
                    'content' => 'admin/add_waiting_point',
                    'scripts' => array('js/bootstrap-datepicker.min.js', 'js/scripts/admin/add_waiting_point.js?t=' . time()),
                    'admin' => $admin,
                    'hour_entries' => $hour_entries,
                    'minute_entries' => $minute_entries,
                    'meridiem_entries' => $meridiem_entries,
                    'purposes' => $this->purposes->get_active_purposes()
                );

                $this->load->view('layout', $data);
            }
            else if($this->input->method() == 'post')
            {
                $required_inputs = array(
                    'waiting_point_name' => 'Waiting Point Name',
                    'purpose_id' => 'Purpose',
                    'date_of_waiting_point' => 'Date',
                    'time_hour' => 'Hour',
                    'time_minute' => 'Minute',
                    'time_meridiem' => 'AM/PM',
                    'waiting_duration' => 'Duration of waiting in minutes'
                );

                foreach($required_inputs as $input_name=>$label)
                {
                    if(strlen($this->input->post($input_name)) == 0)
                    {
                        $this->session->set_flashdata('error_message', $label . " is required");
                        redirect('/admin/add_waiting_point', 'refresh');
                    }

                }

                $this->load->model('purposes');

                $purpose = $this->purposes->get_purpose($this->input->post('purpose_id'));

                if( ! $purpose)
                    throw new Exception("Invalid purpose");

                $this->load->model('waitingPoints');

                $waiting_point_on_datetime = $this->input->post('date_of_waiting_point') .
                    " " .
                    $this->input->post('time_hour') .
                    ":" .
                    $this->input->post('time_minute') .
                    " " .
                    $this->input->post('time_meridiem');

                $waiting_point_on_timestamp = strtotime($waiting_point_on_datetime);

                $new_waiting_point_id = $this->waitingPoints->add_new_waiting_point(
                    $this->input->post('waiting_point_name'),
                    $purpose->id,
                    date("Y-m-d H:i:s", $waiting_point_on_timestamp),
                    $this->input->post('waiting_duration'),
                    $admin->id
                );

                if(! $new_waiting_point_id)
                    throw new Exception("Failed to create the waiting point");

                $this->session->set_flashdata('success_message', "Waiting point is added successfully");
                redirect('/admin/waiting_points', 'refresh');
            }
        }
        catch (Exception $e)
        {
            log_message('debug', 'Exception thrown at: add_waiting_points: '.$e->getMessage());
            show_error($e->getMessage(), 400, "Not Supported");
        }
    }

    private function verify_admin_login()
    {
        if( ! $this->session->has_userdata('managequeue_admin'))
        {

            if($this->input->is_ajax_request())
            {
                echo json_encode(
                    array(
                        'status' => false,
                        'message' => "Please Sign In"
                    )
                );
                return;
            }
            else
                {
                    redirect('/admin/login', 'refresh');
                    return;
                }
        }

        $this->load->model('officers');

        $admin = $this->officers->get_admin(null, $this->session->userdata('managequeue_admin'));

        if(! $admin)
            throw new Exception("Admin not found");

        return $admin;
    }


}