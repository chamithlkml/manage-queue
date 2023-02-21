<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 8/12/18
 * Time: 11:24 PM
 */
class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
        $this->config->load('appconfig', TRUE);
    }

    public function index()
    {

        $data = array(
            'csrf' => array(
                'name' => $this->security->get_csrf_token_name(),
                'hash' => $this->security->get_csrf_hash()
            ),
            'content' => 'home/index',
            'scripts' => array('js/bootstrap-datepicker.min.js', 'js/scripts/home.js?t=' . time())
        );

        $this->load->model('officers');

        if($this->session->has_userdata('managequeue_officer'))
            $data['officer'] = $this->officers->get_officer($this->session->userdata('managequeue_officer'));


        if($this->session->has_userdata('managequeue_admin'))
            $data['admin'] = $this->officers->get_admin(null, $this->session->userdata('managequeue_admin'));

        $data['service_officers'] = $this->officers->get_officers('service_officer');

        $data['officer_title'] = $this->config->item('officer_title', 'appconfig');

        $this->load->model('purposes');

        $data['purposes'] = $this->purposes->get_active_purposes();

        $this->load->view('layout', $data);
    }

}