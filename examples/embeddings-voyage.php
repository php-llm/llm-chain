<?php

use PhpLlm\LlmChain\Voyage\Model\Voyage;
use PhpLlm\LlmChain\Voyage\Platform\Voyage as VoyagePlatform;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

$platform = new VoyagePlatform(HttpClient::create(), $_ENV['VOYAGE_API_KEY']);
$embeddings = new Voyage($platform);

$vector = $embeddings->create(<<<TEXT
    Once upon a time, there was a country called Japan. It was a beautiful country with a lot of mountains and rivers.
    The people of Japan were very kind and hardworking. They loved their country very much and took care of it. The
    country was very peaceful and prosperous. The people lived happily ever after.
    TEXT);

echo 'Dimensions: '.$vector->getDimensions().PHP_EOL;
