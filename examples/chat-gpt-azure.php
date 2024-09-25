<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\Azure;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$platform = new Azure(HttpClient::create(),
    getenv('AZURE_OPENAI_BASEURL'),
    getenv('AZURE_OPENAI_DEPLOYMENT'),
    getenv('AZURE_OPENAI_VERSION'),
    getenv('AZURE_OPENAI_KEY')
);
$llm = new Gpt($platform, Version::gpt4oMini());

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chain->call($messages);

echo $response.PHP_EOL;
