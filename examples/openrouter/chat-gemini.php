<?php

use PhpLlm\LlmChain\Bridge\OpenRouter\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Model;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['OPENROUTER_KEY'])) {
    echo 'Please set the OPENROUTER_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENROUTER_KEY']);
$model = new Model('google/gemini-2.0-flash-exp:free');

$chain = new Chain($platform, $model);
$messages = new MessageBag(
    Message::forSystem('You are a helpful assistant.'),
    Message::ofUser('Tina has one brother and one sister. How many sisters do Tina\'s siblings have?'),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
