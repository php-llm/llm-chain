<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use PhpLlm\LlmChain\ToolBox\ChainSubscriber;
use PhpLlm\LlmChain\ToolBox\Tool\YouTubeTranscriber;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolBox\ToolBox;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$httpClient = HttpClient::create();
$platform = new OpenAI($httpClient, $_ENV['OPENAI_API_KEY']);
$llm = new Gpt($platform, Version::gpt4oMini());

$transcriber = new YouTubeTranscriber($httpClient);
$toolBox = new ToolBox(new ToolAnalyzer(), [$transcriber]);
$processor = new ChainSubscriber($toolBox);
$chain = new Chain($llm, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('Please summarize this video for me: https://www.youtube.com/watch?v=6uXW-ulpj0s'));
$response = $chain->call($messages, [
    'stream' => true, // enable streaming of response text
]);

foreach ($response->getContent() as $word) {
    echo $word;
}
echo PHP_EOL;
