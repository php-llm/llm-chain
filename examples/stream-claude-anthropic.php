<?php

use PhpLlm\LlmChain\Anthropic\Model\Claude;
use PhpLlm\LlmChain\Anthropic\Platform\Anthropic;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['ANTHROPIC_API_KEY'])) {
    echo 'Please set the ANTHROPIC_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = new Anthropic(HttpClient::create(), $_ENV['ANTHROPIC_API_KEY']);
$llm = new Claude($platform);

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are a thoughtful philosopher.'),
    Message::ofUser('What is the purpose of an ant?'),
);
$response = $chain->call($messages, [
    'stream' => true, // enable streaming of response text
]);

assert($response instanceof Generator);

foreach ($response as $word) {
    echo $word;
}
echo PHP_EOL;
