<?php

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\Chain\ToolBox\Tool\Tavily;
use PhpLlm\LlmChain\Chain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY']) || empty($_ENV['TAVILY_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY and TAVILY_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$tavily = new Tavily(HttpClient::create(), $_ENV['TAVILY_API_KEY']);
$toolBox = new ToolBox(new ToolAnalyzer(), [$tavily]);
$processor = new ChainProcessor($toolBox);
$chain = new Chain($platform, $llm, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('What was the latest game result of Dallas Cowboys?'));
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
