<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Model\Language\Gpt;
use PhpLlm\LlmChain\Platform\OpenAI\OpenAI;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = new OpenAI(HttpClient::create(), $_ENV['OPENAI_API_KEY']);
$llm = new Gpt($platform, Gpt::GPT_4O_MINI);

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are an image analyzer bot that helps identify the content of images.'),
    Message::ofUser(
        'Describe the image as a comedian would do it.',
        new Image(dirname(__DIR__).'/tests/Fixture/image.png'),
    ),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
