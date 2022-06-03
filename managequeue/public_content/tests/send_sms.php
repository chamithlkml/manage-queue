<?php
/**
 * Created by PhpStorm.
 * User: chamith
 * Date: 26/8/18
 * Time: 9:04 PM
 */
try{
	$user = "94772999492";
	$password = "3326";
	$text = urlencode("Hello world Chamith");
$to = "94772999492";

$baseurl ="http://www.textit.biz/sendmsg";
$url = "$baseurl/?id=$user&pw=$password&to=$to&text=$text";
$ret = file($url);

print_r($ret);

$res= explode(":",$ret[0]);

if (trim($res[0])=="OK")
{
	echo "Message Sent – ID : ".$res[1];
}
else
{
	echo "Sent Failed – Error : ".$res[1];
}
}catch(Exception $e){
	echo $e->getMessage();
}

