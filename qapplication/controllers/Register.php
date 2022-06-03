<?php
/**
 * Created by Dropsuite Pte Ltd.
 * User: chamith
 * Date: 8/11/18
 * Time: 2:19 PM
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Register extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index()
    {
        $data = array(
            'content' => 'register/index'
        );
        $this->load->view('layout', $data);
    }
}