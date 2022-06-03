<?php

/**
 * Created by ManageQ
 * User: chamith
 * Date: 9/15/18
 * Time: 12:27 PM
 */

class Officers extends CI_Model
{

    public $name;
    public $mobile_number;
    public $mobile_number_verification_code;
    public $verification_code_issued_on;
    public $mobile_number_verified;
    public $role;
    public $deactivated_on;
    public $created_on;
    public $created_by;
    public $deactivated_by;

    function __construct()
    {
        parent::__construct(); // construct the Model class
    }

    public function get_officers($role=null)
    {
        $this->db->from('officers');
        $this->db->where('mobile_number_verified', 1);
        $this->db->where('deactivated_on IS NULL', null, false);

        if(!is_null($role))
            $this->db->where('role', $role);

        return $this->db->get()->result();
    }

    public function get_admin($mobile_number=null, $officer_id=null)
    {
        $this->db->from('officers');

        if(!is_null($mobile_number))
            $this->db->where('mobile_number', $mobile_number);

        if(!is_null($officer_id))
            $this->db->where('id', $officer_id);

        $this->db->where('role', 'administrator');

        return $this->db->get()->row();
    }

    public function issue_new_verification_token($officer_id, $verification_code)
    {
        $this->db->set('mobile_number_verification_code', $verification_code);
        $this->db->set('verification_code_issued_on', date("Y-m-d H:i:s"));
        $this->db->set('mobile_number_verified', 0);
        $this->db->where('id', $officer_id);

        $this->db->update('officers');
    }

    public function get_officer($id=null, $mobile_number=null, $role=null, $mobile_number_verified=null, $is_active=null)
    {
        $this->db->from('officers');

        if(!is_null($id))
            $this->db->where('id', $id);

        if(!is_null($mobile_number))
            $this->db->where('mobile_number', $mobile_number);

        if(!is_null($role))
            $this->db->where('role', $role);

        if(!is_null($mobile_number_verified))
            $this->db->where('mobile_number_verified', $mobile_number_verified);

        if(!is_null($is_active))
            $is_active ? $this->db->where('deactivated_on IS NULL', null, false) : $this->db->where('deactivated_on IS NOT NULL', null, false);

        return $this->db->get()->row();
    }

    public function verify_mobile_number($officers_id, $verification_code)
    {
        $result = $this->get_officer($officers_id);

        if($result)
        {
            $time_diff = time() - DateTime::createFromFormat("Y-m-d H:i:s", $result->verification_code_issued_on)->getTimestamp();

            if($time_diff > 180)
            {
                throw new Exception("Token expired. Please try again.");
            }

            if($result->mobile_number_verification_code == trim($verification_code))
            {
                $this->db->set('mobile_number_verified', 1);
                $this->db->where('id', $officers_id);
                $this->db->update('officers');

                return true;
            }
        }

        return false;
    }

    public function add_new_officer($name, $mobile_number, $role, $mobile_number_verification_code=null)
    {
        $this->name = $name;
        $this->role = $role;
        $this->mobile_number = $mobile_number;

        if(!is_null($mobile_number_verification_code))
        {
            $this->mobile_number_verification_code = $mobile_number_verification_code;
            $this->mobile_number_verified = 0;
            $this->verification_code_issued_on = date("Y-m-d H:i:s");
        }

        $this->db->insert('officers', $this);

        return $this->db->insert_id();
    }

    public function set_mobile_number_verification_code($officers_id, $verification_code)
    {
        $this->db->set('mobile_number_verification_code', $verification_code);
        $this->db->set('verification_code_issued_on', date("Y-m-d H:i:s"));
        $this->db->where('id', $officers_id);
        $this->db->update('officers');
    }

    public function set_creation_data($officers_id, $created_on, $created_by)
    {
        $this->db->set('created_on', $created_on);
        $this->db->set('created_by', $created_by);
        $this->db->where('id', $officers_id);
        $this->db->update('officers');
    }

}