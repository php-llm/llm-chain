<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Model\Language\Gpt;
use PhpLlm\LlmChain\Platform\OpenAI\Azure;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['AZURE_OPENAI_BASEURL']) || empty($_ENV['AZURE_OPENAI_DEPLOYMENT']) || empty($_ENV['AZURE_OPENAI_VERSION']) || empty($_ENV['AZURE_OPENAI_KEY'])
) {
    echo 'Please set the AZURE_OPENAI_BASEURL, AZURE_OPENAI_DEPLOYMENT, AZURE_OPENAI_VERSION, and AZURE_OPENAI_KEY environment variables.'.PHP_EOL;
    exit(1);
}

$platform = new Azure(HttpClient::create(),
    $_ENV['AZURE_OPENAI_BASEURL'],
    $_ENV['AZURE_OPENAI_DEPLOYMENT'],
    $_ENV['AZURE_OPENAI_VERSION'],
    $_ENV['AZURE_OPENAI_KEY'],
);
$llm = new Gpt($platform, Gpt::GPT_4O_MINI);

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
