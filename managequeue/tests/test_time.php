<?php
/**
 * Created by PhpStorm.
 * User: chamith
 * Date: 4/9/18
 * Time: 6:16 AM
 */
$date = "2018-09-28";
$date_office_start = "2018-09-28 08:00:00";
$seconds_diff = strtotime($date_office_start) - strtotime($date);

echo "seconds diff: " . $seconds_diff . "\n";
