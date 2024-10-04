<?php

class EmailController extends BaseController 
{
    /**
     * THE METHOD FOR SENDING THE EMAIL THAT IS FORGOT EMAIL
     * 
     * $message: MESSAGE CONTENT
     * $to: RECEIVER
     * 
     * return {Object}
     */
    public static function sendEmail( $data, $to ) {

        $view = self::getReminderView($data);

        $payload = [
            'options' => [
                'sandbox' => false,
            ],
            'content' => [
                'from' => [
                    'name' => 'SunsHydrogen',
                    'email' => 'info@sunshydrogen.com',
                ],
                'subject' => 'Automation - Password forgot requested',
                'html' => $view,
            ],
            'recipients' => [
                [ 'address' => $to, ],
            ],
        ];
        
        $headers = [ 'Authorization: '.env('MAIL_SPARKPOST_KEY', '') ];
        $result = self::sparkpost('POST', 'transmissions', $payload, $headers);

        return $result;
    }

    /**
     * THE METHOD FOR SENDING THE NOTIFICATION EMAIL
     * 
     * $message: MESSAGE CONTENT
     * $to: RECEIVER
     * 
     * return {Object}
     */
    public static function sendNotifyEmail( $to, $data, $subject ) {

        $payload = [
            'options' => [
                'sandbox' => false,
            ],
            'content' => [
                'from' => [
                    'name' => 'SunsHydrogen',
                    'email' => 'info@sunshydrogen.com',
                ],
                'subject' => $subject,
                'html' => $data,
            ],
            'recipients' => [
                [ 'address' => $to, ],
            ],
        ];
        
        $headers = [ 'Authorization: '.env('MAIL_SPARKPOST_KEY', '') ];
        $result = self::sparkpost('POST', 'transmissions', $payload, $headers);

        return $result;
    }

    /**
     * SENDING THE EMAIL WITH USING THE SPARKPOST
     * 
     * return {Object}
     */
    public static function sparkpost($method, $uri, $payload = [], $headers = []) {

        $defaultHeaders = [ 'Content-Type: application/json' ];
        
        $curl = curl_init();
        $method = strtoupper($method);
        
        $finalHeaders = array_merge($defaultHeaders, $headers);

        $url = 'https://api.sparkpost.com:443/api/v1/'.$uri;

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($method !== 'GET') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $finalHeaders);

        $result = curl_exec($curl);

        curl_close($curl);

        return $result;
    }

    /**
     * Password reseting view
     */
    public static function getReminderView($user) {
        
        $view = "";
        $view .= "<h3>".trans('email.password_reset',  ['site_name'=> Config::get('app.app_title', 'Linkr')])."</h3>";
        $view .= "<div>";
        $view .= trans('email.password_reset_msg')."<br/><br/>".URL::to('password', array($user->activation_code))."<br/>";
        $view .= trans('email.expire_msg', [ 'time'=> 1]);
        $view .= "</div>";

        return $view;
    }
    
}