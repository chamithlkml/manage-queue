<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by ManageQ.
 * User: chamith
 * Date: 22/8/18
 * Time: 11:28 PM
 */

class Tokens extends CI_Model
{

    public $name;
    public $mobile_number;
    public $mobile_number_verification_code;
    public $verification_code_issued_on;
    public $mobile_number_verified;
    public $date_to_office;
    public $queue_no;
    public $queue_no_issued_on;
    public $arrived_to_office_on;
    public $uuid;
    public $expected_service_on;
    public $job_started_on;
    public $job_done_on;
    public $officer_id;
    public $estimated_waiting_time;
    public $purpose_id;

    function __construct()
    {
        parent::__construct(); // construct the Model class
    }

    public function get_queue($id=null, $queue_number=null, $date_to_office=null, $uuid=null)
    {
        $this->db->from('tokens');

        if(!is_null($id))
            $this->db->where('id', $id);

        if(!is_null($queue_number))
            $this->db->where('queue_no', $queue_number);

        if(!is_null($date_to_office))
            $this->db->where('date_to_office', $date_to_office);

        if(!is_null($uuid))
            $this->db->where('uuid', $uuid);

        return $this->db->get()->row();
    }

    public function add_new_token($name, $mobile_number, $date_to_office, $mobile_number_verification_code=null, $officer_id=null, $purpose_id=null)
    {
        $this->name = $name;
        $this->mobile_number = $mobile_number;
        $this->date_to_office = $date_to_office;

        if(!is_null($officer_id))
            $this->officer_id = $officer_id;

        if(!is_null($mobile_number_verification_code))
        {
            $this->mobile_number_verification_code = $mobile_number_verification_code;
            $this->mobile_number_verified = 0;
            $this->verification_code_issued_on = date("Y-m-d H:i:s");
        }
        else
        {
            $this->mobile_number_verified = 1;
        }

        if( ! is_null($purpose_id))
        {
            $this->purpose_id = $purpose_id;
        }

        $this->db->insert('tokens', $this);

        return $this->db->insert_id();
    }

    public function verify_mobile_number($tokens_id, $verification_code)
    {
        $result = $this->get_queue($tokens_id);

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
                $this->db->where('id', $tokens_id);
                $this->db->update('tokens');

                return true;
            }
        }

        return false;
    }

    /**
     * Returns total number of tokens serviced + pending to a given date
     * @param $date_to_office
     * @param $exception_tokens_id
     * @return int
     */
    public function get_total_serviceable_tokens($date_to_office, $exception_tokens_id)
    {
        $this->db->from('tokens');
        $this->db->where('id !=', $exception_tokens_id);
        $this->db->where('date_to_office', $date_to_office);
        $this->db->where('mobile_number_verified', 1);
        $this->db->where('queue_no !=', NULL);

        return $this->db->count_all_results();
    }

    public function get_last_pending_queue_token($date_to_office, $exception_tokens_id, $purpose_id=null)
    {
        $this->db->from('tokens');
        $this->db->where('id !=', $exception_tokens_id);
        $this->db->where('date_to_office', $date_to_office);
        $this->db->where('mobile_number_verified', 1);
        $this->db->where('expected_service_on !=', NULL);
        $this->db->where('job_done_on', NULL);
        $this->db->where('queue_no !=', NULL);

        if(!is_null($purpose_id))
            $this->db->where('purpose_id', $purpose_id);

        $this->db->order_by('queue_no', 'desc');

        return $this->db->get()->row();
    }

    public function issue_queue_position(
        $tokens_id,
        $service_length,
        $office_opens_at,
        $office_closes_at,
        $max_queue_tickets_count,
        $service_breaks,
        $special_service_breaks,
        $uuid_length,
        $officer_id=null
    )
    {

        $new_token_details = $this->get_queue($tokens_id);

        $purpose = $this->get_purpose($new_token_details->purpose_id);

        log_message('debug', '$purpose: ' . print_r($purpose, true));

        $last_queue_token = $this->get_last_pending_queue_token($new_token_details->date_to_office, $tokens_id);

        log_message('debug', '$last_queue_token: ' . print_r($last_queue_token, true));

        $total_serviceable_tokens = $this->get_total_serviceable_tokens($new_token_details->date_to_office, $tokens_id);

        log_message('debug', '$total_serviceable_tokens: ' . print_r($total_serviceable_tokens, true));
        log_message('debug', '$max_queue_tickets_count: ' . $max_queue_tickets_count);

        if($total_serviceable_tokens >= $max_queue_tickets_count)
        {
            throw new Exception("All queue tickets have been issued. Please try on a different date.");
        }

        $new_queue_no = 1;
        $current_timestamp = time();
        $office_start_timestamp = strtotime($new_token_details->date_to_office . " " . $office_opens_at);
        $expected_service_on_timestamp =  ($current_timestamp > $office_start_timestamp) ? $current_timestamp : $office_start_timestamp;

        $waiting_points_for_same_purpose = $this->filter_out_old_waiting_points(
            $this->get_today_waiting_points_by_purpose($new_token_details->purpose_id)
        );

        log_message('debug', '$waiting_points_for_same_purpose: ' . print_r($waiting_points_for_same_purpose, true));

        if(count($waiting_points_for_same_purpose) > 0)
        {
            if($last_queue_token)
            {
                $new_queue_no = $last_queue_token->queue_no + 1;
            }

            $now_timestamp = time();

            foreach($waiting_points_for_same_purpose as $waiting_point)
            {
                $waiting_point_timestamp = strtotime($waiting_point->waiting_point_on);

                if($now_timestamp <= $waiting_point_timestamp)
                {
                    $expected_service_on_timestamp = strtotime($waiting_point->waiting_point_on) + $waiting_point->duration*60;
                    break;
                }
            }

            # get_last_pending_queue_token($date_to_office, $exception_tokens_id, $purpose_id=null)
            $last_token_with_same_purpose = $this->get_last_pending_queue_token($new_token_details->date_to_office, $tokens_id, $new_token_details->purpose_id);


            if($last_token_with_same_purpose)
            {
                $expected_service_on_timestamp = strtotime($last_token_with_same_purpose->expected_service_on) + $service_length*60;
            }

        }else
            {
                #Purposes with no waiting points

                if($last_queue_token)
                {
                    $new_queue_no = $last_queue_token->queue_no + 1;

                    $purposes_with_no_waiting_points = $this->get_purposes_with_no_waiting_points();

                    log_message('debug', '$purposes_with_no_waiting_points: ' . print_r($purposes_with_no_waiting_points, true));

                    foreach($purposes_with_no_waiting_points as $purpose_with_no_waiting_points)
                    {
                        $last_token_with_no_waiting_point_purpose = $this->get_last_pending_queue_token(
                            $new_token_details->date_to_office,
                            $new_token_details->id,
                            $purpose_with_no_waiting_points->id
                        );

                        if($last_token_with_no_waiting_point_purpose)
                        {
                            $expected_service_on_timestamp = strtotime($last_token_with_no_waiting_point_purpose->expected_service_on) + $service_length*60;
                            break;
                        }
                    }
                }
            }

        $today_waiting_points = $this->filter_out_old_waiting_points(
            $this->get_today_waiting_points()
        );

        $waiting_points_service_breaks = $this->get_waiting_points_service_breaks($today_waiting_points);

        log_message('debug', '$waiting_points_service_breaks: ' . print_r($waiting_points_service_breaks, true));

        $expected_service_on_timestamp = $this->escape_service_breaks(
            $waiting_points_service_breaks,
            date("Y-m-d"),
            $expected_service_on_timestamp,
            $service_length
        );

        log_message('debug', '$expected_service_on_timestamp: ' . print_r($expected_service_on_timestamp, true));

        $special_service_breaks_on_date = $this->get_special_service_breaks_on_date(
            $special_service_breaks,
            $new_token_details->date_to_office
        );

        $expected_service_on_timestamp = $this->escape_service_breaks(
            (!is_null($special_service_breaks_on_date)) ? $special_service_breaks_on_date : $service_breaks,
            $new_token_details->date_to_office,
            $expected_service_on_timestamp,
            $service_length
        );

        $office_close_timestamp = strtotime($new_token_details->date_to_office . " " . $office_closes_at);

        if($expected_service_on_timestamp > ($office_close_timestamp - ($service_length*60)))
        {
            throw new Exception("No time left to serve you on this date. Please try on a different date.");
        }

        if($expected_service_on_timestamp < $current_timestamp)
        {
            $expected_service_on_timestamp = $current_timestamp;
        }

        $this->db->set('queue_no', $new_queue_no);
        $this->db->set('queue_no_issued_on', date("Y-m-d H:i:s"));
        $this->db->set('expected_service_on', date("Y-m-d H:i:s", $expected_service_on_timestamp));
        $this->db->set('uuid', $this->get_uuid($uuid_length));

        if(! is_null($officer_id))
            $this->db->set('officer_id', $officer_id);

        $this->db->where('id', $tokens_id);
        $this->db->update('tokens');

        return $this->get_queue($tokens_id);
    }

    private function get_waiting_points_service_breaks($waiting_points)
    {
        $service_breaks = array();

        foreach($waiting_points as $waiting_point){
            $waiting_point_chunks = explode(" ", $waiting_point->waiting_point_on);
            $service_breaks[$waiting_point_chunks[1]] = $waiting_point->duration;
        }

        return $service_breaks;
    }

    private function get_uuid($length)
    {
        $this->load->library('Utilities');

        $random_key = $this->utilities->generate_random_string($length);

        $existing_random_key_entry = $this->get_queue(
            null,
            null,
            null,
            $random_key
        );

        if($existing_random_key_entry)
            $random_key = $this->get_uuid($length);

        return $random_key;
    }

    private function get_special_service_breaks_on_date($special_service_breaks, $date_to_office)
    {
        foreach($special_service_breaks as $special_service_break_date => $special_service_breaks_on_date)
        {
            if($special_service_break_date === $date_to_office)
                return $special_service_breaks_on_date;
        }

        return null;
    }

    private function escape_service_breaks($service_breaks, $date_to_office, $expected_service_on_timestamp, $service_length)
    {
        foreach($service_breaks as $break_start => $break_length)
        {
            $break_start_timestamp = strtotime($date_to_office . " " . $break_start);

            $break_end_timestamp = $break_start_timestamp + $break_length*60;

            $expected_service_end_timestamp = $expected_service_on_timestamp + $service_length*60;

            if(($break_start_timestamp < $expected_service_on_timestamp) && ($expected_service_on_timestamp < $break_end_timestamp))
            {
                unset($service_breaks[$break_start]);
                return $this->escape_service_breaks($service_breaks, $date_to_office, $break_end_timestamp, $service_length);
            }

            if(($expected_service_end_timestamp > $break_start_timestamp) && ($expected_service_end_timestamp < $break_end_timestamp))
            {
                unset($service_breaks[$break_start]);
                return $this->escape_service_breaks($service_breaks, $date_to_office, $break_end_timestamp, $service_length);
            }

            if(($break_start_timestamp > $expected_service_on_timestamp) && ($break_end_timestamp < $expected_service_end_timestamp))
            {
                unset($service_breaks[$break_start]);
                return $this->escape_service_breaks($service_breaks, $date_to_office, $break_end_timestamp, $service_length);
            }

        }

        return $expected_service_on_timestamp;
    }

    public function re_issue_verification_code($tokens_id, $verification_code)
    {
        $this->db->set('mobile_number_verification_code', $verification_code);
        $this->db->set('verification_code_issued_on', date("Y-m-d H:i:s"));
        $this->db->set('mobile_number_verified', 0);
        $this->db->where('id', $tokens_id);
        $this->db->update('tokens');
    }

    public function get_existing_tokens_count($mobile_number, $date_to_office)
    {
        $this->db->from('tokens');
        $this->db->where('mobile_number', $mobile_number);
        $this->db->where('date_to_office', $date_to_office);
        $this->db->where('mobile_number_verified', 1);
        $this->db->where('queue_no !=', NULL);

        return $this->db->get()->num_rows();
    }

    public function set_arrival_to_office($tokens_id, $estimated_waiting_time_seconds)
    {
        $estimated_waiting_time_minutes = ceil($estimated_waiting_time_seconds/60);

        $this->set(
            $tokens_id,
            'estimated_waiting_time',
            $estimated_waiting_time_minutes
        );

        return $this->set(
            $tokens_id,
            'arrived_to_office_on',
            date("Y-m-d H:i:s")
        );
    }

    public function get_status($tokens_id)
    {
        $token = $this->get_queue($tokens_id);

        if(!is_null($token->job_done_on))
            return "Service given on " . $token->job_done_on;

        if(!is_null($token->job_started_on))
            return "Service started on " . $token->job_started_on;

        if(!is_null($token->arrived_to_office_on))
            return "Customer arrived to office on " . $token->arrived_to_office_on;

        if(!is_null($token->queue_no_issued_on))
            return "Queue number issued on " . $token->queue_no_issued_on;

        return "Status not found";
    }

    private function set($id, $column, $value)
    {
        $this->db->set($column, $value);
        $this->db->where('id', $id);
        $this->db->update('tokens');
    }

    public function get_last_job_started_token($date_to_office=null, $officer_id=null, $purpose_id=null)
    {
        $this->db->from('tokens');
        $this->db->order_by('job_started_on', 'desc');
        $this->db->limit(1);
        $this->db->where('job_started_on !=', NULL);
        $this->db->where('job_done_on', NULL);

        if(! is_null($officer_id))
        {
            $this->db->where('officer_id', $officer_id);
        }

        if( ! is_null($date_to_office))
        {
            $this->db->where('date_to_office', $date_to_office);
        }
        else
            {
                $this->db->where('date_to_office', date("Y-m-d"));
            }

        if(! is_null($purpose_id))
            $this->db->where('purpose_id', $purpose_id);

        return $this->db->get()->row();
    }

    public function get_next_service_token()
    {
        $this->db->from('tokens');
        $this->db->order_by('arrived_to_office_on', 'asc');
        $this->db->limit(1);
        $this->db->where('job_started_on', NULL);
        $this->db->where('job_done_on', NULL);
        $this->db->where('arrived_to_office_on !=', NULL);
        $this->db->where('date_to_office', date("Y-m-d"));

        return $this->db->get()->row();
    }

    public function set_service_started($tokens_id, $officer_id)
    {
        $this->set(
            $tokens_id,
            'job_started_on',
            date("Y-m-d H:i:s")
        );

        $this->set(
            $tokens_id,
            'officer_id',
            $officer_id
        );
    }

    public function get_service_started_token($officer_id)
    {
        $this->db->from('tokens');
        $this->db->where('job_started_on !=', NULL);
        $this->db->where('job_done_on', NULL);
        $this->db->where('date_to_office', date("Y-m-d"));
        $this->db->where('officer_id', $officer_id);

        return $this->db->get()->row();
    }

    public function set_service_given($tokens_id)
    {
        $this->set(
            $tokens_id,
            'job_done_on',
            date("Y-m-d H:i:s")
        );
    }

    public function get_current_servicing_tokens()
    {
        $this->db->from('tokens');
        $this->db->where('job_started_on !=', NULL);
        $this->db->where('job_done_on', NULL);
        $this->db->where('date_to_office', date("Y-m-d"));

        return $this->db->get()->row();
    }

    public function get_estimated_waiting_time($tokens_id)
    {
        $token = $this->get_queue($tokens_id);

        if(! $token)
            throw new Exception("Token not found");

    }

    public function get_last_token_expected_service_time($officer_id, $date_to_office, $office_open_time)
    {
        $this->db->from('tokens');
        $this->db->order_by('expected_service_on', 'desc');
        $this->db->limit(1);
        $this->db->where('date_to_office', $date_to_office);
        $this->db->where('officer_id', $officer_id);
        $this->db->where('expected_service_on !=', NULL);

        $last_service_token = $this->db->get()->row();

        log_message('debug', '$last_service_token: ' . print_r($last_service_token, true));

        $date_to_office_timestamp = strtotime($date_to_office);

        $date_to_office_std = date("Y-m-d", $date_to_office_timestamp);

        $last_service_token_expected_service_time = $date_to_office_std . " " . $office_open_time;

        if($last_service_token)
        {
            $last_service_token_expected_service_time = $last_service_token->expected_service_on;
        }else
            {
                log_message('debug', "last_service_token not found");
            }

        $timestamp_now = time();
        $timestamp_last_token_service_time = strtotime($last_service_token_expected_service_time);

        if($timestamp_last_token_service_time < $timestamp_now)
        {
            $last_service_token_expected_service_time = date("Y-m-d H:i:s");
        }

        return $last_service_token_expected_service_time;
    }

    public function get_last_office_arrived_token($purpose_id=null)
    {
        $this->db->from('tokens');
        $this->db->where('date_to_office', date("Y-m-d"));
        $this->db->where('arrived_to_office_on !=', NULL);
        $this->db->where('job_started_on', NULL);

        if(! is_null($purpose_id))
            $this->db->where('purpose_id', $purpose_id);

        $this->db->order_by('arrived_to_office_on', 'desc');
        $this->db->limit(1);

        return $this->db->get()->row();
    }

    private function get_purpose($purpose_id)
    {
        $CI =& get_instance();
        $CI->load->model('purposes');

        $purpose = $CI->purposes->get_purpose($purpose_id);

        if( ! $purpose)
            throw new Exception("Purpose not found in Tokens");

        return $purpose;
    }

    private function get_today_waiting_points_by_purpose($purpose_id)
    {
        $CI =& get_instance();
        $CI->load->model('waitingPoints');
        $today = date("Y-m-d");

        return $CI->waitingPoints->get_waiting_points($purpose_id, $today);
    }

    private function get_purposes_with_no_waiting_points()
    {
        $CI =& get_instance();
        $CI->load->model('purposes');
        $CI->load->model('waitingPoints');

        $active_purposes = $CI->purposes->get_active_purposes();

        $purposes_with_no_waiting_points = array();

        foreach($active_purposes as $active_purpose)
        {
            $today_waiting_points_for_purpose = $this->get_today_waiting_points_by_purpose($active_purpose->id);

            if(count($today_waiting_points_for_purpose) == 0){
                $purposes_with_no_waiting_points[] = $active_purpose;
            }
        }

        return $purposes_with_no_waiting_points;
    }

    private function get_today_waiting_points()
    {
        $CI =& get_instance();
        $CI->load->model('waitingPoints');
        $today = date("Y-m-d");

        return $CI->waitingPoints->get_waiting_points(null, $today);
    }

    private function filter_out_old_waiting_points($waiting_points)
    {
        $filtered_waiting_points = array();
        $now = time();

        foreach($waiting_points as $waiting_point)
        {
           $waiting_point_on_timestamp = strtotime($waiting_point->waiting_point_on);

           if($waiting_point_on_timestamp > $now)
               $filtered_waiting_points[] = $waiting_point;
        }

        return $filtered_waiting_points;
    }
}