<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
 * User: chamith
 * Date: 11/4/18
 * Time: 5:16 PM
 */
class WaitingPoints extends CI_Model
{
    public $name;
    public $purpose_id;
    public $waiting_point_on;
    public $duration;
    public $created_on;
    public $deleted_on;
    public $created_by;

    function __construct()
    {
        parent::__construct();
    }

    public function add_new_waiting_point($name, $purpose_id, $waiting_point_on, $duration, $created_by)
    {
        $this->name = $name;
        $this->purpose_id = $purpose_id;
        $this->waiting_point_on = $waiting_point_on;
        $this->duration = $duration;
        $this->created_on = date("Y-m-d H:i:s");
        $this->created_by = $created_by;

        $this->db->insert('waiting_points', $this);

        return $this->db->insert_id();
    }

    public function get_active_waiting_points()
    {
        $this->db->from('waiting_points');
        $this->db->where('deleted_on', NULL);
        $this->db->order_by('id', 'desc');

        return $this->db->get()->result();
    }

    public function get_waiting_points($purpose_id=null, $date=null)
    {
        $this->db->from('waiting_points');
        $this->db->where('deleted_on', NULL);

        if(!is_null($purpose_id))
            $this->db->where('purpose_id', $purpose_id);

        if(!is_null($date))
            $this->db->like('waiting_point_on', $date, 'after');

        $this->db->order_by('waiting_point_on', 'asc');

        return $this->db->get()->result();
    }

}