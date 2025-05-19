<?php

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Brave;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Crawler;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['OPENAI_API_KEY']) || empty($_ENV['BRAVE_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY and BRAVE_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$httpClient = HttpClient::create();
$brave = new Brave($httpClient, $_ENV['BRAVE_API_KEY']);
$crawler = new Crawler($httpClient);
$toolbox = Toolbox::create($brave, $crawler);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $llm, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('What was the latest game result of Dallas Cowboys?'));
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
