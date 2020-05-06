<?php
      require 'vendor/autoload.php';
      use Aws\DynamoDb\DynamoDbClient;
      use Aws\Lambda\LambdaClient;
      use Aws\DynamoDb\Exception\DynamoDbException;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
$to = $_GET["emailaddress"];
$subject = "Contrasena temporal creada ";

if($_GET["emailaddress"]!='' &&$_GET["id"]!=''){

        include('../config_aws.php');
    try{
        $lamdba = LambdaClient::factory(
            array(
            'key'    => awsAccessKey,
            'secret' => awsSecretKey,
            'region' => 'us-east-1',
            'version' => 'latest'
            )
        );

        $newpass = generateRandomString();
        $resultpass = $lamdba->invoke(array(
            // FunctionName is required
            'FunctionName' => 'Encrypt',
            'InvocationType' => 'RequestResponse',
            'Payload' => '{"action":"encrypt","text":"'.$newpass.'"}'
        ));
        $result = json_decode($resultpass->get('Payload')->__toString());
        $response =$result;
         // Set Amazon s3 credentials
        $idusers = $_GET['id'];
        $conn = pg_connect(connString);
        $resultSql = pg_query($conn, "SELECT * FROM person where id ='$idusers'");
        $items = pg_fetch_all($resultSql) ;
        if($items[0] && array_key_exists("request_password",$items[0]) && $items[0]["request_password"] == 1){

            $iduser = $items[0]["id"];
            $resultSql = pg_query($conn, "UPDATE person set password = '$response' , request_password = 0 where id ='$iduser'");
            pg_close($conn);

            $message = file_get_contents("newpassword.txt");
            $message = str_replace('{{EMAILADDRESS}}',$_GET["emailaddress"],$message);
            $message = str_replace('{{PASSWORD}}',$newpass,$message);

            $email = new \SendGrid\Mail\Mail(); 
            $email->setFrom("informacion@sertigapps.com", "Sertig Apps");
            $email->setSubject($subject);
            $email->addTo($to, $items[0]->name . " " . $items[0]->lastname);
            $email->addContent("text/html", $message);
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
                echo "Ooops algo salio mal";
        }else{
                echo file_get_contents("passwordseted.txt");
        }
    }
    catch(DynamoDbException $error){

        echo $error->getMessage();
    }
}
else{
    echo "Contraseña no generada ";
};
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
?>
