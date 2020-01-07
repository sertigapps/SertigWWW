<?php
require 'vendor/autoload.php';
     use Aws\DynamoDb\DynamoDbClient;
     use Aws\DynamoDb\Exception\DynamoDbException;
 header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
$data = json_decode($request_body);
$request_body = file_get_contents('php://input');
$data = json_decode($request_body);
$to = "lrglm16@gmail.com";


        include('../config_aws.php');
   
            $message = 'Nombre : ' . $data->name . ' <br>' . 'Email : ' . $data->email . ' <br>' . 'Phone : ' . $data->phone . ' <br>' . 'Description : ' . $data->description . ' <br>' ;
            // Always set content-type when sending HTML email
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "X-Priority: 3\r\n";
            $headers .= "X-Mailer: PHP". phpversion() ."\r\n"; 

            // More headers
            $headers .= "Organization: Sertig Apps\r\n";
            $headers .= 'From: Sertig Appps<info@sertigapps.com>' . "\r\n";
            $headers .= 'Reply-To: Sertig Apps<info@sertigapps.com>' . "\r\n";

            mail($to,$subject,$message,$headers);
       

?>
