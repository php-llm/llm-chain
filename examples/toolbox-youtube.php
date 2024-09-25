<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use PhpLlm\LlmChain\ToolBox\Tool\YouTubeTranscriber;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolBox\ToolBox;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';

$httpClient = HttpClient::create();
$platform = new OpenAI($httpClient, getenv('OPENAI_API_KEY'));
$llm = new Gpt($platform, Version::gpt4oMini());

$transcriber = new YouTubeTranscriber($httpClient);
$toolBox = new ToolBox(new ToolAnalyzer(), [$transcriber]);
$chain = new Chain($llm, $toolBox);

$messages = new MessageBag(Message::ofUser('Please summarize this video for me: https://www.youtube.com/watch?v=6uXW-ulpj0s'));
$response = $chain->call($messages);

echo $response.PHP_EOL;
