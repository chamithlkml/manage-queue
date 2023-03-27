<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by Chamith Jayaweera
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
        parent::__construct();
    }

    /**
     * Returns a token object find by id, queue number, date to office, uuid
     *
     * @param integer|null|null $id
     * @param string|null|null $queue_number
     * @param string|null|null $date_to_office
     * @param string|null|null $uuid
     * @return object
     */
    public function get_queue(
        int|null $id = null,
        string|null $queue_number = null,
        string|null $date_to_office = null,
        string|null $uuid = null): object
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

    /**
     * Create a new token object and returns inserted id
     *
     * @param string $name
     * @param string $mobile_number
     * @param string $date_to_office
     * @param string|null|null $mobile_number_verification_code
     * @param integer|null|null $officer_id
     * @param integer|null|null $purpose_id
     * @return int
     */
    public function add_new_token(
        string $name,
        string $mobile_number,
        string $date_to_office,
        string|null $mobile_number_verification_code = null,
        int|null $officer_id = null,
        int|null $purpose_id = null
        ): int
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

    /**
     * Verify mobile number with the verification code
     *
     * @param integer $tokens_id
     * @param string $verification_code
     * @return boolean
     */
    public function verify_mobile_number(int $tokens_id, string $verification_code): bool
    {
        $result = $this->get_queue($tokens_id);
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
                $this->db->where('id', $tokens_id);
                $this->db->update('tokens');

                $mobile_number_verified = true;
            }
        }

        return $mobile_number_verified;
    }

    /**
     * Returns total number of tokens serviced + pending to a given date
     * @param $date_to_office
     * @param $exception_tokens_id
     * @return int
     */
    public function get_total_serviceable_tokens(string $date_to_office, int $exception_tokens_id): int
    {
        $this->db->from('tokens');
        $this->db->where('id !=', $exception_tokens_id);
        $this->db->where('date_to_office', $date_to_office);
        $this->db->where('mobile_number_verified', 1);
        $this->db->where('queue_no !=', NULL);

        return $this->db->count_all_results();
    }

    /**
     * Returns last pending queue token object
     *
     * @param string $date_to_office
     * @param string $exception_tokens_id
     * @param integer|null|null $purpose_id
     * @return object
     */
    public function get_last_pending_queue_token(string $date_to_office, string $exception_tokens_id, int|null $purpose_id = null): object
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
        int $tokens_id,
        int $service_length,
        string $office_opens_at,
        string $office_closes_at,
        int $max_queue_tickets_count,
        array $service_breaks,
        array $special_service_breaks,
        int $uuid_length,
        int $officer_id = null
    ): object
    {

        $new_token_details = $this->get_queue($tokens_id);

        $purpose = $this->get_purpose($new_token_details->purpose_id);

        $last_queue_token = $this->get_last_pending_queue_token($new_token_details->date_to_office, $tokens_id);

        $total_serviceable_tokens = $this->get_total_serviceable_tokens($new_token_details->date_to_office, $tokens_id);

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

        $expected_service_on_timestamp = $this->escape_service_breaks(
            $waiting_points_service_breaks,
            date("Y-m-d"),
            $expected_service_on_timestamp,
            $service_length
        );

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

    /**
     * Returns status string depending on token id
     *
     * @param integer $tokens_id
     * @return string
     */
    public function get_status(int $tokens_id): string
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

    /**
     * Set value to a given column search by id
     *
     * @param integer $id
     * @param string $column
     * @param integer|string $value
     * @return void
     */
    private function set(int $id, string $column, int|string $value): void
    {
        $this->db->set($column, $value);
        $this->db->where('id', $id);
        $this->db->update('tokens');
    }

    /**
     * Returns last started job's token object
     *
     * @param string|null|null $date_to_office
     * @param string|null|null $officer_id
     * @param string|null|null $purpose_id
     * @return object
     */
    public function get_last_job_started_token(string|null $date_to_office = null, string|null $officer_id = null, string|null $purpose_id = null): object
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

    /**
     * Returns the next service token object
     *
     * @return object
     */
    public function get_next_service_token(): object
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

    /**
     * Set service started date of a given token, officer id
     *
     * @param integer $tokens_id
     * @param integer $officer_id
     * @return void
     */
    public function set_service_started(int $tokens_id, int $officer_id): void
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

    /**
     * Returns service started token object of a given officer id
     *
     * @param integer $officer_id
     * @return object
     */
    public function get_service_started_token(int $officer_id): object
    {
        $this->db->from('tokens');
        $this->db->where('job_started_on !=', NULL);
        $this->db->where('job_done_on', NULL);
        $this->db->where('date_to_office', date("Y-m-d"));
        $this->db->where('officer_id', $officer_id);

        return $this->db->get()->row();
    }

    /**
     * Set service is given
     *
     * @param integer $tokens_id
     * @return void
     */
    public function set_service_given(int $tokens_id): void
    {
        $this->set(
            $tokens_id,
            'job_done_on',
            date("Y-m-d H:i:s")
        );
    }

    /**
     * Returns current servicing token object
     *
     * @return object
     */
    public function get_current_servicing_tokens(): object
    {
        $this->db->from('tokens');
        $this->db->where('job_started_on !=', NULL);
        $this->db->where('job_done_on', NULL);
        $this->db->where('date_to_office', date("Y-m-d"));

        return $this->db->get()->row();
    }

    /**
     * Returns last token's expected service time
     *
     * @param integer $officer_id
     * @param string $date_to_office
     * @param string $office_open_time
     * @return string
     */
    public function get_last_token_expected_service_time(int $officer_id, string $date_to_office, string $office_open_time): string
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

    /**
     * Returns last office arrived token object
     *
     * @param integer|null|null $purpose_id
     * @return object
     */
    public function get_last_office_arrived_token(int|null $purpose_id = null): object
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

    /**
     * Returns purpose object
     *
     * @param integer $purpose_id
     * @return object
     */
    private function get_purpose(int $purpose_id): object
    {
        $CI =& get_instance();
        $CI->load->model('purposes');

        $purpose = $CI->purposes->get_purpose($purpose_id);

        if( ! $purpose)
            throw new Exception("Purpose not found in Tokens");

        return $purpose;
    }

    /**
     * Returns an array of waiting point objects filtered by purpose id
     *
     * @param integer $purpose_id
     * @return array
     */
    private function get_today_waiting_points_by_purpose(int $purpose_id): array
    {
        $CI =& get_instance();
        $CI->load->model('waitingPoints');
        $today = date("Y-m-d");

        return $CI->waitingPoints->get_waiting_points($purpose_id, $today);
    }

    /**
     * Returns an array of puroposes with no waiting points
     *
     * @return array
     */
    private function get_purposes_with_no_waiting_points(): array
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

    /**
     * Returns an array of waiting points fallen into today
     *
     * @return array
     */
    private function get_today_waiting_points(): array
    {
        $CI =& get_instance();
        $CI->load->model('waitingPoints');
        $today = date("Y-m-d");

        return $CI->waitingPoints->get_waiting_points(null, $today);
    }

    /**
     * Returns new waiting points by removing olds
     *
     * @param array $waiting_points
     * @return array
     */
    private function filter_out_old_waiting_points(array $waiting_points): array
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
