<?php

use PhpLlm\LlmChain\Anthropic\Model\Claude;
use PhpLlm\LlmChain\Anthropic\Platform\Anthropic;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\Message\UserMessage;
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
    new SystemMessage('You are a pirate and you write funny.'),
    new UserMessage('What is the Symfony framework?'),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
