<?php

use PhpLlm\LlmChain\Bridge\Mistral\Mistral;
use PhpLlm\LlmChain\Bridge\Mistral\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['MISTRAL_API_KEY'])) {
    echo 'Please set the REPLICATE_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['MISTRAL_API_KEY']);
$llm = new Mistral();
$chain = new Chain($platform, $llm);

$messages = new MessageBag(Message::ofUser('What is the best French cheese?'));
$response = $chain->call($messages, [
    'temperature' => 0.7,
]);

echo $response->getContent().PHP_EOL;
