<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Created by ManageQ.
 * User: chamith
 * Date: 28/8/18
 * Time: 10:44 PM
 */
$config['app_name'] = "ManageQ";

/**
 * http://textit.biz/accountoverview.php
 */
$config['textit_username'] = "94772999492";
$config['textit_password'] = "3326";
$config['textit_baseurl'] = "http://www.textit.biz/sendmsg";

/**
 * Service related configs
 */

$config['service_length'] = 15;#Mean time takes to serve a single client in minutes
$config['office_opens_at'] = '04:30:00';
$config['office_closes_at'] = '23:00:00';
$config['max_queue_tickets_count'] = 150;
# mobile-number => max-tokens
$config['agent_restrictions'] = array(
    '0772999492' => 150,
    '0766405986' => 20
);
# 'start-time' => 'length in minutes'
$config['service_breaks'] = array(
    '10:00:00' => 15, #Tea break
    '12:30:00' => 60, #Lunch break
    '15:00:00' => 15 #Tea break
);

$config['special_service_breaks'] = array(
    '2018-09-17' => array( #Director arrives
        '08:00:00' => 60, #Staff meeting
        '11:30:00' => 30, #Security drill
        '14:00:00' => 40, #Director speech
    ),
    '2018-09-18' => array( #Minister arrives
        '08:20:00' => 60, #Staff meeting
        '11:18:00' => 30, #Security drill
        '14:20:00' => 40, #Minister speech
    )
);

$config['time_afters'] = array(
    '09:00:00',
    '10:00:00'
);

$config['officers'] = array(
    array('Chamith Jayaweera', '0772999492', 'administrator')
);

$config['officer_roles'] = array(
    'receptionist' => 'Receptionist',
    'service_officer' => 'Service Officer'
);

$config['token_uuid_length'] = 4;

#minutes
$config['reception_to_office_walk_time'] = 2;

#Show date to office fixed to date to receptionist
$config['show_date_to_office_fixed_to_date'] = true;

$config['send_token_without_verification_by_receptionist'] = true;

$config['show_current_servicing_queue_no'] = true;

#officer,doctor etc
$config['officer_title'] = "officer";

$config['mark_arrival_with_token'] = TRUE;

$config['purposes'] = array(
    array('name' => "Pawn Jewelry", 'type' => "pawn_jewelry"),
    array('name' => "Collect Jewelry", 'type' => "collect_jewelry")
);

$config['other_purpose_id'] = 3;
