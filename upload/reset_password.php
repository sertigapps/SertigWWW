<?php
      require 'vendor/autoload.php';
      use Aws\DynamoDb\DynamoDbClient;
      use Aws\Lambda\LambdaClient;
      use Aws\DynamoDb\Exception\DynamoDbException;
header("Access-Control-Allow-Origin: *");
$to = $_GET["emailaddress"];
$subject = "Contrasena temporal creada ";

if($_GET["emailaddress"]!='' &&$_GET["id"]!=''){

        include('../config_aws.php');
    try{
        $lamdba = LambdaClient::factory(
            array(
            'key'    => awsAccessKey,
            'secret' => awsSecretKey,
            'region' => 'us-east-1'
            )
        );

        $newpass = generateRandomString();
        $resultpass = $lamdba->invoke(array(
            // FunctionName is required
            'FunctionName' => 'Encrypt',
            'InvocationType' => 'RequestResponse',
            'Payload' => '{"action":"encrypt","text":"'.$newpass.'"}'
        ));
        $response = $resultpass->getAll()["result"];
         // Set Amazon s3 credentials
        $client = DynamoDbClient::factory(
            array(
            'key'    => awsAccessKey,
            'secret' => awsSecretKey,
            'region' => 'us-east-1'
            )
        );
        $iduser = $_GET['id'];
        $conn = pg_connect(connString);
        $resultSql = pg_query($conn, "SELECT * FROM person where id ='$iduser'");
        $items = pg_fetch_all($resultSql) ;
        if($items[0] && array_key_exists("request_password",$items[0]) && $items[0]["request_password"]["N"] == "1"){

            $iduser = $items[0]["id"];
            $resultSql = pg_query($conn, "UPDATE person set request_password = '$response' , request_password = 1 where id ='$iduser'");
            pg_close($conn);

            $message = file_get_contents("newpassword.txt");
            $message = str_replace('{{EMAILADDRESS}}',$_GET["emailaddress"],$message);
            $message = str_replace('{{PASSWORD}}',$newpass,$message);

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
