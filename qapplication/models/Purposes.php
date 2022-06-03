<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by ManageQ.
 * User: chamith
 * Date: 10/30/18
 * Time: 4:56 AM
 */
class Purposes extends CI_Model
{
    public $name;
    public $type;
    public $deactivated_on;
    public $created_on;
    public $created_by;
    public $deactivated_by;

    function __construct()
    {
        parent::__construct(); // construct the Model class
    }

    public function get_active_purposes()
    {

        $this->db->from('purposes');
        $this->db->where('deactivated_by', NULL);
        $this->db->order_by('id', 'desc');

        return $this->db->get()->result();
    }

    public function get_purpose($id=null, $name=null)
    {
        $this->db->from('purposes');

        if( ! is_null($id))
            $this->db->where('id', $id);

        if( ! is_null($name))
            $this->db->where('name', trim($name));

        $this->db->where('deactivated_on', NULL);

        return $this->db->get()->row();
    }

    public function add_new_purpose($name, $created_by)
    {
        $existing_purpose = $this->get_purpose(null, $name);

        if($existing_purpose)
            throw new Exception("Purpose already exists with the name: " . $name);

        $this->name = trim($name);
        $this->type = $this->derive_purpose_type($name);
        $this->created_on = date("Y-m-d H:i:s");
        $this->created_by = $created_by;

        $this->db->insert('purposes', $this);

        return $this->db->insert_id();
    }

    private function derive_purpose_type($purpose_name)
    {
        if(strlen($purpose_name) == 0)
            throw new Exception("Purpose Name length must be non zero");

        $str_chunks = explode(" ", trim(strtolower($purpose_name)));
        $purpose_type = "";

        foreach($str_chunks as $k=>$str_chunk)
        {
            if(strlen($str_chunk) > 0)
                $purpose_type .= ($k < (count($str_chunks) - 1)) ? $str_chunk . "_" : $str_chunk;
        }

        return $purpose_type;
    }

    public function get_purposes_with_no_waiting_points()
    {
        $this->db->from('purposes');

    }
}