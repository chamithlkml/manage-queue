<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: chamith
 * Date: 26/8/18
 * Time: 6:19 AM
 */

class Utilities
{
    private $ci;

    public function __construct()
    {
        $this->ci = & get_instance();
    }

    public function generate_random_number($length=4)
    {
        $key_store = '1234567890';

        return self::generate_random_key($length, $key_store);
    }

    public function generate_random_string($length=6)
    {
        $key_store = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return self::generate_random_key($length, $key_store);
    }

    private function generate_random_key($length=null, $key_store=null)
    {
        if(is_nulL($length))
            throw new Exception("Key length is required");

        if(is_null($key_store))
            throw new Exception("Key store is required");

        $random_key = '';

        while(strlen($random_key) < $length)
        {
            $random_key .= substr($key_store, rand(0, (strlen($key_store) - 1)), 1);
        }

        return $random_key;
    }

    public function is_from_valid_agent($mobile_number, $existing_tokens_count)
    {
        $this->ci->config->load('appconfig', TRUE);
        $agent_restrictions = $this->ci->config->item('agent_restrictions', 'appconfig');

        foreach($agent_restrictions as $agent_mobile_no => $tokens_limit)
        {
            if($mobile_number === $agent_mobile_no && $existing_tokens_count < $tokens_limit)
                return true;
        }

        return false;
    }

    public function get_time_minutes_seconds_format($time_seconds)
    {

       $minutes = ceil($time_seconds/60);

       $hours = null;

       if($minutes > 60)
       {
           $hours = ($minutes - ($minutes%60))/60;
           $minutes = $minutes - ($hours*60);
       }

       $formatted_time = is_null($hours) ? $minutes . " minute(s)" : $hours .
           " hour(s) and " . $minutes . " minute(s)";

       return $formatted_time;
    }

    public function is_deletable_officer($officer)
    {
        return $officer->role !== 'administrator';
    }

    public function get_srilankan_standardized_phone_number($international_no)
    {
        if(substr($international_no, 0, 3) != "+94")
            throw new Exception('Mobile no: ' . $international_no . ' seems not from Sri Lanka');

        return str_replace("+94", "0", $international_no);
    }
}