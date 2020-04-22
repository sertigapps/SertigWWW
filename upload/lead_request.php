<?php
require 'vendor/autoload.php';
     use Aws\DynamoDb\DynamoDbClient;
     use Aws\DynamoDb\Exception\DynamoDbException;
 header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
$request_body = file_get_contents('php://input');
$data = json_decode($request_body);
$to = "lrglm16@gmail.com";
$subject = "Fil 4:13 Lead";


        include('../config_aws.php');
   
            $message = 'Nombre : ' . $_POST['name'] . ' <br>' . 'Email : ' . $_POST['email'] . ' <br>' . 'Phone : ' . $_POST['phone'] . ' <br>' . 'Description : ' . $_POST['description'] . ' <br>' ;
            $email = new \SendGrid\Mail\Mail(); 
            $email->setFrom("informacion@sertigapps.com", "Sertig Apps");
            $email->setSubject($subject);
            $email->addTo($to, "Fil 4:13");
            $email->addContent("text/plain", $message);
            $sendgrid = new \SendGrid(SENDGRID_API_KEY);
            try {
                $response = $sendgrid->send($email);
            } catch (Exception $e) {
                $iduser = false;
            }

?>
