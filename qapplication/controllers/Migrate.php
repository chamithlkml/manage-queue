<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by ManageQ.
 * User: chamith
 * Date: 8/22/18
 * Time: 7:31 PM
 */
class Migrate extends CI_Controller
{
    public function index()
    {
        $this->load->library('migration');

        if($this->migration->current() === FALSE)
        {
            show_error($this->migration->error_string());
        }
        else
        {
            echo 'Successfully migrated';
        }
    }
}