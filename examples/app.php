<?php
require __DIR__ ."/../vendor/autoload.php";

use CloudLayerIO\Client as CloudLayer;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');


//initialize client with api key

CloudLayer::config([
    'api_key' => $_ENV['CLOUD_LAYER_API_KEY']
]);

try {
    $status = CloudLayer::getStatus();
    $rateLimit = CloudLayer::getRateLimit();
    $rateLimitRemaining = CloudLayer::getRateLimitRemaining();
    $rateLimitReset = CloudLayer::getRateLimitReset();
} catch (\CloudLayerIO\Exceptions\UnauthorizedUsage $exception) {
    echo $exception->getMessage();
}
