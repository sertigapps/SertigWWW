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
        $result = $client->query(array(
            'TableName'     => 'person',
            'KeyConditions' => array(
                'id' => array(
                    'AttributeValueList' => array(
                        array('N' => $_GET['id'])
                    ),
                    'ComparisonOperator' => 'EQ'
                )
            )
        ));
        $items = $result["Items"];
        if($items[0]){

            $result = $client->updateItem(array(
                // TableName is required
                'TableName' => 'person',
                // Key is required
                'Key' => array(
                    // Associative array of custom 'AttributeName' key names
                    'id' => array(
                        'N' => $_GET["id"])),
                'AttributeUpdates' => array(
                    // Associative array of custom 'AttributeName' key names
                    'password' => array(
                        'Value' => array(
                            'S' => $response)))));
            $iduser = $items[0]["id"]["N"];
            $message = file_get_contents("newpassword.txt");
            $message = str_replace('{{EMAILADDRESS}}',$_GET["emailaddress"],$message);
            $message = str_replace('{{PASSWORD}}',$newpass,$message);

            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

            // More headers
            $headers .= 'From: <info@sertigapps.com>' . "\r\n";

            mail($to,$subject,$message,$headers);
        }
        else{
            $iduser=false;
        }

        if(!$iduser){
                echo "Contraseña generada sin exito";
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
