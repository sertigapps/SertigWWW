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
            'region' => 'us-east-1'
            )
        );

        $result = $client->query(array(
            'TableName'     => 'person',
            'IndexName'     => 'emailaddress-password-index',
            'KeyConditions' => array(
                'emailaddress' => array(
                    'AttributeValueList' => array(
                        array('S' => $data->emailaddress)
                    ),
                    'ComparisonOperator' => 'EQ'
                )
            )
        ));
        $items = $result["Items"];
        if($items[0]){
            $iduser = $items[0]["id"]["N"];
            $message = "<html><head><title>Recupera tu contraseña</title></head><body><p>Sigue el enlace para recuperar acceso a tu cuenta</p><table><tr><th>Username / Email ".$data->emailaddress."</th></tr><tr><td> <a href=\"http://www.sertigapps.com/upload/reset_password.php?id=".$iduser."&emailaddress=".$data->emailaddress."\">Recuperar</a> </td></tr></table></body></html>";

            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // More headers
            $headers .= 'From: <mailer@sertigapps.com>' . "\r\n";

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
