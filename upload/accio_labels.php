<?php
    // Include the SDK using the Composer autoloader
   require 'vendor/autoload.php';
     use Aws\S3\S3Client;
     use Aws\Rekognition\RekognitionClient;
     use Aws\Translate\TranslateClient;
     use Aws\S3\Exception\S3Exception;


include('lumos_image_validation.php'); // getExtension Method
$valid = true;
$angularJSData = json_decode(file_get_contents("php://input"));
$angularJSData = (array)$angularJSData;

if(! isset($angularJSData['sertig_app']) ||! isset($angularJSData['sertig_token']) ||! isset($angularJSData['sertig_email']) ||! isset($angularJSData['image_url'])){
        $message = "{\"error\":true,\"message\":\"token and email not defined\"}";
    $valid = false;
}
if($_SERVER['REQUEST_METHOD'] == "POST" && $valid)
{
        // File size validation
        include('../config_aws.php');
        //Rename image name.
        // Bucket Name
        $bucket="sertigs3";
        $rClient =RekognitionClient::factory(
            array(
            'key'    => awsAccessKey,
            'secret' => awsSecretKey,
            'region' => 'us-east-1',
            'version' => 'latest'
            )
        );
        $tClient =TranslateClient::factory(
            array(
            'key'    => awsAccessKey,
            'secret' => awsSecretKey,
            'region' => 'us-east-1',
            'version' => 'latest'
            )
        );
        $image_name_actual =$name;

        try {
               
                try {
                $labels = $rClient->detectLabels([
                    'Image' => [ // REQUIRED
                        'S3Object' => [
                            'Bucket' => $bucket,
                            'Name' => $angularJSData['sertig_app']."/". $angularJSData['image_url']
                        ],
                        'MinConfidence' => 95,
                    ]
                ]);
                $englishLables = [];
                foreach($labels['Labels'] as $label){
                    $englishLables[] = $label['Name'];
                }
                $message = implode(',',$englishLables);
                
                $resultT = $client->translateText([
                    'SourceLanguageCode' => 'en', // REQUIRED
                    'TargetLanguageCode' => 'es', // REQUIRED
                    'Text' =>  $message, // REQUIRED
                ]);
                $message = $resultT;
                } catch (S3Exception $e) {
                // Catch an S3 specific exception.
                echo $e->getMessage();
            }

            } catch (S3Exception $e) {
                // Catch an S3 specific exception.
                echo $e->getMessage();
            }
}
echo $message;
?>
