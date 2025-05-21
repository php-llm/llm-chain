<?php

use PhpLlm\LlmChain\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Bridge\Google\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\YouTubeTranscriber;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['GOOGLE_API_KEY'])) {
    echo 'Please set the GOOGLE_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['GOOGLE_API_KEY']);
$llm = new Gemini(Gemini::GEMINI_2_FLASH);

$transcriber = new YouTubeTranscriber(HttpClient::create());
$toolbox = Toolbox::create($transcriber);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $llm, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('Please summarize this video for me https://www.youtube.com/watch?v=6uXW-ulpj0s with the video ID as 6uXW-ulpj0s'));
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
