<?php

use PhpLlm\LlmChain\Anthropic\Model\Claude;
use PhpLlm\LlmChain\Anthropic\Platform\Anthropic;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$platform = new Anthropic(HttpClient::create(), getenv('ANTHROPIC_API_KEY'));
$llm = new Claude($platform);

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chain->call($messages);

echo $response.PHP_EOL;
