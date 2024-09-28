<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use PhpLlm\LlmChain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\ToolBox\Tool\SerpApi;
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

$serpApi = new SerpApi($httpClient, $_ENV['SERP_API_KEY']);
$toolBox = new ToolBox(new ToolAnalyzer(), [$serpApi]);
$processor = new ChainProcessor($toolBox);
$chain = new Chain($llm, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('Who is the current chancellor of Germany?'));
$response = $chain->call($messages);

echo $response.PHP_EOL;
