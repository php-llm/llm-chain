<?php

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$chain = new Chain($platform, $llm);
$messages = new MessageBag(
    Message::forSystem('You are an image analyzer bot that helps identify the content of images.'),
    Message::ofUser(
        'Describe the image as a comedian would do it.',
        Image::fromFile(dirname(__DIR__, 2).'/tests/Fixture/image.jpg'),
    ),
);
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
