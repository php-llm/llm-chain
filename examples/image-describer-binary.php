<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\Message\UserMessage;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = new OpenAI(HttpClient::create(), $_ENV['OPENAI_API_KEY']);
$llm = new Gpt($platform, Version::gpt4oMini());

$chain = new Chain($llm);
$messages = new MessageBag(
    new SystemMessage('You are an image analyzer bot that helps identify the content of images.'),
    new UserMessage(
        'Describe the image as a comedian would do it.',
        new Image(dirname(__DIR__).'/tests/Fixture/image.png'),
    ),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
