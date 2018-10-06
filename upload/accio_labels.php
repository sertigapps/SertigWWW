<?php
    // Include the SDK using the Composer autoloader
   require 'vendor/autoload.php';
     use Aws\S3\S3Client;
     use Aws\Rekognition\RekognitionClient;
     use Aws\S3\Exception\S3Exception;


include('lumos_image_validation.php'); // getExtension Method
$valid = true;
if(!$_POST['sertig_app']||!$_POST['sertig_token']||!$_POST['sertig_email']||!$_POST['url_image']){
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
        $image_name_actual =$name;

        try {
               
                try {
                $labels = $rClient->detectLabels([
                    'Image' => [ // REQUIRED
                        'S3Object' => [
                            'Bucket' => $bucket,
                            'Name' => $_POST['sertig_app']."/". $_POST['url_image']
                        ],
                    ]
                ]);
                $englishLables = [];
                foreach($labels['Labels'] as $label){
                    $englishLables[] = $label['Name'];
                }
                $message = implode(' ',$englishLables);
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
