<?php
use App\Models\User;

function pr($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

function sendEmail($to, $subject, $message, $from_email='info@kayhanaudio.com.au', $from_name='Kayhan Audio'){
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: <'.$from_email.'>' . "\r\n";

    mail($to,$subject,$message,$headers);
    return true;

}