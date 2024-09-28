<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Model\Language\Gpt;
use PhpLlm\LlmChain\Platform\OpenAI\OpenAI;
use PhpLlm\LlmChain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\ToolBox\Tool\OpenMeteo;
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
$llm = new Gpt($platform, Gpt::GPT_4O_MINI);

$wikipedia = new OpenMeteo($httpClient);
$toolBox = new ToolBox(new ToolAnalyzer(), [$wikipedia]);
$processor = new ChainProcessor($toolBox);
$chain = new Chain($llm, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('How is the weather currently in Berlin?'));
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
