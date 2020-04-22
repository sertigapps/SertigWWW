<?php
require 'vendor/autoload.php';
     use Aws\DynamoDb\DynamoDbClient;
     use Aws\DynamoDb\Exception\DynamoDbException;
 header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
$request_body = file_get_contents('php://input');
$data = json_decode($request_body);
$to = $data->emailaddress;
$subject = "Recuperar Acceso para ".$data->app;

if($data->emailaddress!=''){

        include('../config_aws.php');
    try{  
        $conn = pg_connect(connString);
        $resultSql = pg_query($conn, "SELECT * FROM person where emailaddress ='$data->emailaddress'");
        $items = pg_fetch_all($resultSql) ;
        if($items[0]){
            $iduser = $items[0]["id"];
            $resultSql = pg_query($conn, "UPDATE person set request_password = 1 where id ='$iduser'");
            pg_close($conn);
            $message = file_get_contents("emailrecovery.txt");
            $message = str_replace('{{EMAILADDRESS}}',$data->emailaddress,$message); 
            $message = str_replace('{{link}}',"http://www.sertigapps.com/upload/alohomora_reset.php?id=".$iduser."&emailaddress=".$data->emailaddress,$message);
            mail($to,$subject,$message,$headers);
            $email = new \SendGrid\Mail\Mail(); 
            $email->setFrom("informacion@sertigapps.com", "Sertig Apps");
            $email->setSubject($subject);
            $email->addTo($data->emailaddress, $data->name . " " . $data->lastname);
            $email->addContent("text/plain", $message);
            $sendgrid = new \SendGrid(SENDGRID_API_KEY);
            try {
                $response = $sendgrid->send($email);
            } catch (Exception $e) {
                $iduser = false;
            }
        }
        else{
            $iduser=false;
        }
        if(!$iduser){
                echo "{\"error\":false,\"emailaddress\":\"".$data->emailaddress."\"}";
        }else{
                echo "{\"id\":".$iduser."}";
        }
    }
    catch(DynamoDbException $error){
        echo $error->getMessage();
    }
}
else{   
    echo "{\"error\":true,\"emailaddress\":\"".$data->emailaddress."\"}";
};
?>
