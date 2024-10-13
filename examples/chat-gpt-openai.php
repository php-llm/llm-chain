<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Model\Language\Gpt;
use PhpLlm\LlmChain\Platform\OpenAI\OpenAI;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = new OpenAI(HttpClient::create(), $_ENV['OPENAI_API_KEY']);
$llm = new Gpt($platform, Gpt::GPT_4O_MINI, [
    'temperature' => 0.5, // default options for the model
]);

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chain->call($messages, [
    'max_tokens' => 500, // specific options just for this call
]);

echo $response->getContent().PHP_EOL;
