<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Runtime\OpenAI;
use Symfony\Component\HttpClient\HttpClient;
use PhpLlm\LlmChain\Message\Content\Image;

require_once dirname(__DIR__).'/vendor/autoload.php';

$runtime = new OpenAI(HttpClient::create(), getenv('OPENAI_API_KEY'));
$llm = new Gpt($runtime, Version::gpt4oMini());

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are an image analyzer bot that helps identify the content of images.'),
    Message::ofUser(
        'Describe the images as a comedian would do it.',
        new Image('https://upload.wikimedia.org/wikipedia/commons/thumb/3/31/Webysther_20160423_-_Elephpant.svg/350px-Webysther_20160423_-_Elephpant.svg.png'),
		new Image('https://upload.wikimedia.org/wikipedia/commons/thumb/3/37/African_Bush_Elephant.jpg/320px-African_Bush_Elephant.jpg'),
    ),
);
$response = $chain->call($messages);

echo $response.PHP_EOL;
