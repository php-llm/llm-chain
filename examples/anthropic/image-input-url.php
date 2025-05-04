<?php

use PhpLlm\LlmChain\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Bridge\Anthropic\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\Content\ImageUrl;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['ANTHROPIC_API_KEY'])) {
    echo 'Please set the ANTHROPIC_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['ANTHROPIC_API_KEY']);
$llm = new Claude(Claude::SONNET_37);

$chain = new Chain($platform, $llm);
$messages = new MessageBag(
    Message::forSystem('You are an image analyzer bot that helps identify the content of images.'),
    Message::ofUser(
        new ImageUrl('https://upload.wikimedia.org/wikipedia/commons/a/a7/Camponotus_flavomarginatus_ant.jpg'),
        'Describe this image.',
    ),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
