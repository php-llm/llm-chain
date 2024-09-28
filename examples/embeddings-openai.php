<?php

use PhpLlm\LlmChain\OpenAI\Model\Embeddings;
use PhpLlm\LlmChain\OpenAI\Model\Embeddings\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

$platform = new OpenAI(HttpClient::create(), $_ENV['OPENAI_API_KEY']);
$embeddings = new Embeddings($platform, Version::textEmbedding3Small());

$vector = $embeddings->create(<<<TEXT
    Once upon a time, there was a country called Japan. It was a beautiful country with a lot of mountains and rivers.
    The people of Japan were very kind and hardworking. They loved their country very much and took care of it. The
    country was very peaceful and prosperous. The people lived happily ever after.
    TEXT);

echo 'Dimensions: '.$vector->getDimensions().PHP_EOL;
