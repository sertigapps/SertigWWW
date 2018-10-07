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
                            ]
                        ],
                    'MinConfidence' => 95,
                ]);
                $englishLables = [];
                $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
                foreach($labels['Labels'] as $label){
                    $englishLables[] = $label['Name'], $unwanted_array ); ;
                }
                $message = implode(',',$englishLables);
                $message = strtr( $message, $unwanted_array )
                $resultT = $tClient->translateText([
                    'SourceLanguageCode' => 'en', // REQUIRED
                    'TargetLanguageCode' => 'es', // REQUIRED
                    'Text' =>  $message, // REQUIRED
                ]);
                $message =  "{\"error\":false,\"message\":\"".$resultT->get('TranslatedText')."\"}";
                } catch (S3Exception $e) {
                // Catch an S3 specific exception.
                 $message =  "{\"error\":true,\"message\":\"".$e->getMessage()."\"}";
            }

            } catch (S3Exception $e) {
                // Catch an S3 specific exception.
                 $message =  "{\"error\":true,\"message\":\"".$e->getMessage()."\"}";
            }
}
echo $message;
?>
