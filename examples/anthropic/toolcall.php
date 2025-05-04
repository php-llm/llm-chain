<?php

use PhpLlm\LlmChain\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Bridge\Anthropic\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Wikipedia;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['ANTHROPIC_API_KEY'])) {
    echo 'Please set the ANTHROPIC_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['ANTHROPIC_API_KEY']);
$llm = new Claude();

$wikipedia = new Wikipedia(HttpClient::create());
$toolbox = Toolbox::create($wikipedia);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $llm, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('Who is the current chancellor of Germany?'));
$response = $chain->call($messages);

echo $response->getContent().PHP_EOL;
