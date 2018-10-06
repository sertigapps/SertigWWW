<?php
    // Include the SDK using the Composer autoloader
   require 'vendor/autoload.php';
     use Aws\S3\S3Client;
     use Aws\Rekognition\RekognitionClient;
     use Aws\S3\Exception\S3Exception;


include('lumos_image_validation.php'); // getExtension Method
$valid = true;
if(!$_POST['sertig_app']||!$_POST['sertig_token']||!$_POST['sertig_email']){
        $message = "{\"error\":true,\"message\":\"token and email not defined\"}";
    $valid = false;
}
if($_SERVER['REQUEST_METHOD'] == "POST" && $valid)
{
    $name = $_FILES['file']['name'];
    $size = $_FILES['file']['size'];
    $tmp = $_FILES['file']['tmp_name'];
    $ext = getExtension($name);

    if(strlen($name) > 0)
    {
        // File format validation
        if(in_array($ext,$valid_formats))
        {
        // File size validation
        include('../config_aws.php');
        //Rename image name.
        // Bucket Name
        $bucket="sertigs3";


        // Set Amazon s3 credentials
        $client = S3Client::factory(
            array(
            'key'    => awsAccessKey,
            'secret' => awsSecretKey,
            'region' => 'us-east-1',
            'version' => 'latest'
            )
        );
        $image_name_actual =$name;

        try {
                $client->putObject(array(
                    'Bucket'=>$bucket,
                    'Key' => $_POST['sertig_app']."/". $image_name_actual,
                    'SourceFile' => $tmp,
                    'StorageClass' => 'REDUCED_REDUNDANCY'
                ));
                $sourceProperties = getimagesize($tmp);
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $imageType = $sourceProperties[2];


                switch ($imageType) {


                    case IMAGETYPE_PNG:
                        $imageResourceId = imagecreatefrompng($tmp);
                        $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                        imagepng($targetLayer,"thumb/thumb_". $name);
                        break;


                    case IMAGETYPE_GIF:
                        $imageResourceId = imagecreatefromgif($tmp);
                        $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                        imagegif($targetLayer,"thumb/thumb_". $name);
                        break;


                    case IMAGETYPE_JPEG:
                        $imageResourceId = imagecreatefromjpeg($tmp);
                        $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                        imagejpeg($targetLayer,"thumb/thumb_". $name);
                        break;
                    default:
                        echo "Invalid Image type.";
                        exit;
                        break;
                }
                try {
                $client->putObject(array(
                    'Bucket'=>$bucket,
                    'Key' => $_POST['sertig_app']."/thumb/". $image_name_actual,
                    'SourceFile' => "thumb/thumb_".$name,
                    'StorageClass' => 'REDUCED_REDUNDANCY'
                ));
                unlink("thumb/thumb_".$name);
                $message = "{\"success\":\"S3 Upload Successful.\"}";
                } catch (S3Exception $e) {
                // Catch an S3 specific exception.
                echo $e->getMessage();
            }

            } catch (S3Exception $e) {
                // Catch an S3 specific exception.
                echo $e->getMessage();
            }
        }
        else{
            $message = "{\"error\":true,\"message\":\"Invalid file, please upload image file.\"}";
        }
    }else {
    $message = "{\"error\":true,\"message\":\"Please select image file.\"}";
    }
}
echo $message;
function imageResize($imageResourceId,$width,$height) {


    $targetWidth =200;
    $targetHeight =200;
    if($width > $height)
    {
        $thumb_w    =   $targetWidth;
        $thumb_h    =   $height*($targetHeight/$width);
    }

    if($width< $height)
    {
        $thumb_w    =   $width*($targetWidth/$height);
        $thumb_h    =   $targetHeight;
    }

    if($width == $height)
    {
        $thumb_w    =   $targetWidth;
        $thumb_h    =   $targetHeight;
    }

    $targetLayer=imagecreatetruecolor($thumb_w,$thumb_h);
    imagecopyresampled($targetLayer,$imageResourceId,0,0,0,0,$thumb_w,$thumb_h, $width,$height);


    return $targetLayer;
}
?>
