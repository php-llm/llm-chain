<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\ImageUrl;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Runtime\OpenAI;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$runtime = new OpenAI(HttpClient::create(), getenv('OPENAI_API_KEY'));
$llm = new Gpt($runtime, Version::GPT_4o);

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are an image analyzer that looks to images like a comedian would like.'),
    Message::ofUser('Analyze the image.'),
    Message::ofUser(new ImageUrl('https://upload.wikimedia.org/wikipedia/commons/thumb/3/31/Webysther_20160423_-_Elephpant.svg/350px-Webysther_20160423_-_Elephpant.svg.png')),
);
$response = $chain->call($messages);

echo $response.PHP_EOL;
