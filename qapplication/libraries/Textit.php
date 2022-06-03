<?php
/**
 * Created by PhpStorm.
 * User: chamith
 * Date: 28/8/18
 * Time: 10:28 PM
 */


class Textit
{
    private $username;
    private $password;
    private $api_url;

    public function __construct($params)
    {

        $this->api_url = $params['api_url'];
        $this->username = $params['username'];
        $this->password = $params['password'];
    }

    public function send_message($to, $message){
        $to_mobile_no = "94" . substr($to, 1, (strlen($to) - 1));
        $text = urlencode($message);

        $url = "{$this->api_url}?id={$this->username}&pw={$this->password}&to={$to_mobile_no}&text={$text}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $res = curl_exec($ch);

        log_message('debug', '$res: ' . print_r($res, true));

        $error_message = curl_error($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response_blocks = explode(":",$res);

        if(trim($response_blocks[0]) != "OK")
        {
            throw new Exception("Sent Failed – Error : " . $response_blocks[1] . " error_message: " . $error_message . " code: " . $code, $code);
        }

        return true;
    }
}