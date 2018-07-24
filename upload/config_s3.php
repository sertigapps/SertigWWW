<?php
// Bucket Name
$bucket="sertigs3";

//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJRZMXDWWCOOTBWUQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

    // Set Amazon s3 credentials
      $client = S3Client::factory(
      array(
      'key'    => awsAccessKey,
      'secret' => awsSecretKey
       )
      );
?>