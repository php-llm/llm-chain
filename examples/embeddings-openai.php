<?php

use PhpLlm\LlmChain\Model\Embeddings\OpenAI as Embeddings;
use PhpLlm\LlmChain\Platform\OpenAI\OpenAI as Platform;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = new Platform(HttpClient::create(), $_ENV['OPENAI_API_KEY']);
$embeddings = new Embeddings($platform);

$vector = $embeddings->create(<<<TEXT
    Once upon a time, there was a country called Japan. It was a beautiful country with a lot of mountains and rivers.
    The people of Japan were very kind and hardworking. They loved their country very much and took care of it. The
    country was very peaceful and prosperous. The people lived happily ever after.
    TEXT);

echo 'Dimensions: '.$vector->getDimensions().PHP_EOL;
