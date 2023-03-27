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

    /**
     * Returns an array of active, mobile number verified officer objects
     *
     * @param string|null $role
     * @return array
     */
    public function get_officers(string|null $role = null): array
    {
        $this->db->from('officers');
        $this->db->where('mobile_number_verified', 1);
        $this->db->where('deactivated_on IS NULL', null, false);

        if(!is_null($role))
            $this->db->where('role', $role);

        return $this->db->get()->result();
    }

    /**
     * Returns admin object
     *
     * @param string|null $mobile_number
     * @param int|null $officer_id
     * @return object
     */
    public function get_admin(string|null $mobile_number = null, int|null $officer_id = null): object
    {
        $this->db->from('officers');

        if(!is_null($mobile_number))
            $this->db->where('mobile_number', $mobile_number);

        if(!is_null($officer_id))
            $this->db->where('id', $officer_id);

        $this->db->where('role', 'administrator');

        return $this->db->get()->row();
    }

    /**
     * Update mobile_number_verification_code of a given officer
     *
     * @param integer $officer_id
     * @param string $verification_code
     * @return void
     */
    public function issue_new_verification_token(int $officer_id, string $verification_code): void
    {
        $this->db->set('mobile_number_verification_code', $verification_code);
        $this->db->set('verification_code_issued_on', date("Y-m-d H:i:s"));
        $this->db->set('mobile_number_verified', 0);
        $this->db->where('id', $officer_id);

        $this->db->update('officers');
    }

    /**
     * Returns officer object searched by id, mobile number, role, mobile number verified?, active?
     *
     * @param integer|null|null $id
     * @param string|null|null $mobile_number
     * @param string|null|null $role
     * @param string|null|null $mobile_number_verified
     * @param boolean|null|null $is_active
     * @return object
     */
    public function get_officer(
        int|null $id = null,
        string|null $mobile_number = null,
        string|null $role = null, string|null
        $mobile_number_verified = null,
        bool|null $is_active = null): object
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

    /**
     * Verify mobile number with the verification code and returns true if verified
     *
     * @param integer $officers_id
     * @param string $verification_code
     * @return boolean
     */
    public function verify_mobile_number(int $officers_id, string $verification_code): bool
    {
        $result = $this->get_officer($officers_id);
        $mobile_number_verified = false;

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

                $mobile_number_verified = true;
            }
        }

        return $mobile_number_verified;
    }

    /**
     * Create a new officer entry and returns insert id
     *
     * @param string $name
     * @param string $mobile_number
     * @param string $role
     * @param string|null|null $mobile_number_verification_code
     * @return integer
     */
    public function add_new_officer(string $name, string $mobile_number, string $role, string|null $mobile_number_verification_code = null): int
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

    /**
     * Set mobile number verification code of an officer object
     *
     * @param integer $officers_id
     * @param string $verification_code
     * @return void
     */
    public function set_mobile_number_verification_code(int $officers_id, string $verification_code): void
    {
        $this->db->set('mobile_number_verification_code', $verification_code);
        $this->db->set('verification_code_issued_on', date("Y-m-d H:i:s"));
        $this->db->where('id', $officers_id);
        $this->db->update('officers');
    }

    /**
     * Set officer creation date
     *
     * @param integer $officers_id
     * @param string $created_on
     * @param integer $created_by
     * @return void
     */
    public function set_creation_data(int $officers_id, string $created_on, int $created_by): void
    {
        $this->db->set('created_on', $created_on);
        $this->db->set('created_by', $created_by);
        $this->db->where('id', $officers_id);
        $this->db->update('officers');
    }

}
