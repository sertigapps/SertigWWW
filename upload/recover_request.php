<?php
require 'vendor/autoload.php';
     use Aws\DynamoDb\DynamoDbClient;
     use Aws\DynamoDb\Exception\DynamoDbException;
 header("Access-Control-Allow-Origin: *");
$data = json_decode($request_body);
$request_body = file_get_contents('php://input');
$data = json_decode($request_body);
$to = $data->emailaddress;
$subject = "Recuperar Acceso para ".$data->app;

if($data->emailaddress!=''){

        include('../config_aws.php');
    try{    // Set Amazon s3 credentials
        $client = DynamoDbClient::factory(
            array(
            'key'    => awsAccessKey,
            'secret' => awsSecretKey,
            'region' => 'us-east-1',
            'version' => 'latest'
            )
        );
        $conn = pg_connect(connString);
        $resultSql = pg_query($conn, "SELECT * FROM person where emailaddress ='$data->emailaddress'");
        $items = pg_fetch_all($resultSql) ;
        if($items[0]){
            $iduser = $items[0]["id"];
            $resultSql = pg_query($conn, "UPDATE person set request_password = 1 where id ='$iduser'");
            pg_close($conn);
            $message = file_get_contents("emailrecovery.txt");
            $message = str_replace('{{EMAILADDRESS}}',$data->emailaddress,$message); 
            $message = str_replace('{{link}}',"http://www.sertigapps.com/upload/reset_password.php?id=".$iduser."&emailaddress=".$data->emailaddress,$message);
            // Always set content-type when sending HTML email
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "X-Priority: 3\r\n";
            $headers .= "X-Mailer: PHP". phpversion() ."\r\n"; 

            // More headers
            $headers .= "Organization: Sertig Apps\r\n";
            $headers .= 'From: Chapin Bay<info@sertigapps.com>' . "\r\n";
            $headers .= 'Reply-To: Chapin Bay<info@sertigapps.com>' . "\r\n";

            mail($to,$subject,$message,$headers);
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
