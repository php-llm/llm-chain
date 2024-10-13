<?php

use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Bridge\Replicate\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['REPLICATE_API_KEY'])) {
    echo 'Please set the REPLICATE_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['REPLICATE_API_KEY']);
$llm = new Llama();

$chain = new Chain($platform, $llm);
$messages = new MessageBag(
    Message::forSystem('You are a helpful assistant.'),
    Message::ofUser('Tina has one brother and one sister. How many sisters do Tina\'s siblings have?'),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
