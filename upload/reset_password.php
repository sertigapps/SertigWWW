<?php
      require 'vendor/autoload.php';
      use Aws\DynamoDb\DynamoDbClient;
      use Aws\DynamoDb\Exception\DynamoDbException;
header("Access-Control-Allow-Origin: *");
$to = $_GET["emailaddress"];
$subject = "Contrasena temporal creada ";

if($_GET["emailaddress"]!='' &&$_GET["id"]!=''){

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
                            'S' => 'a02dae2144a3289a106e')))));
            $iduser = $items[0]["id"]["N"];
            $message = "<html><head><title>Contraseña temporal creada</title></head><body><p>Tu Contraseña temporal ha sido creada</p><table><tr><th>Username / Email ".$_GET["emailaddress"]."</th></tr><tr><td> Nueva Contraseña : labarraapp</td></tr></table></body></html>";

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
?>
