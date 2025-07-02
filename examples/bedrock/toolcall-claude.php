<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Wikipedia;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (!$_ENV['AWS_ACCESS_KEY_ID'] || !$_ENV['AWS_SECRET_ACCESS_KEY'] || !$_ENV['AWS_DEFAULT_REGION']
) {
    echo 'Please set the AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY and AWS_DEFAULT_REGION environment variables.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create();
$model = new Claude();

$wikipedia = new Wikipedia(HttpClient::create());
$toolbox = Toolbox::create($wikipedia);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $model, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('Who is the current chancellor of Germany?'));
$response = $chain->call($messages);

echo $response->getContent().\PHP_EOL;
