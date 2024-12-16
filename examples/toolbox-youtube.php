<?php

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\Chain\ToolBox\Tool\YouTubeTranscriber;
use PhpLlm\LlmChain\Chain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$transcriber = new YouTubeTranscriber(HttpClient::create());
$toolBox = new ToolBox(new ToolAnalyzer(), [$transcriber]);
$processor = (new ChainProcessor())->withToolBox($toolBox);
$chain = new Chain($platform, $llm);

$messages = new MessageBag(Message::ofUser('Please summarize this video for me: https://www.youtube.com/watch?v=6uXW-ulpj0s'));
$response = $chain->process($messages, [
    'stream' => true, // enable streaming of response text
], $processor);

foreach ($response->getContent() as $word) {
    echo $word;
}
echo PHP_EOL;
