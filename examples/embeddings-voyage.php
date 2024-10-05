<?php

use PhpLlm\LlmChain\Model\Embeddings\Voyage;
use PhpLlm\LlmChain\Platform\Voyage as Platform;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['VOYAGE_API_KEY'])) {
    echo 'Please set the VOYAGE_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = new Platform(HttpClient::create(), $_ENV['VOYAGE_API_KEY']);
$embeddings = new Voyage($platform);

$vector = $embeddings->create(<<<TEXT
    Once upon a time, there was a country called Japan. It was a beautiful country with a lot of mountains and rivers.
    The people of Japan were very kind and hardworking. They loved their country very much and took care of it. The
    country was very peaceful and prosperous. The people lived happily ever after.
    TEXT);

echo 'Dimensions: '.$vector->getDimensions().PHP_EOL;
