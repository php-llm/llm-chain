<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Platform\Bridge\Azure\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (!$_ENV['AZURE_OPENAI_BASEURL'] || !$_ENV['AZURE_OPENAI_GPT_DEPLOYMENT'] || !$_ENV['AZURE_OPENAI_GPT_API_VERSION'] || !$_ENV['AZURE_OPENAI_KEY']
) {
    echo 'Please set the AZURE_OPENAI_BASEURL, AZURE_OPENAI_GPT_DEPLOYMENT, AZURE_OPENAI_GPT_API_VERSION, and AZURE_OPENAI_KEY environment variables.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create(
    $_ENV['AZURE_OPENAI_BASEURL'],
    $_ENV['AZURE_OPENAI_GPT_DEPLOYMENT'],
    $_ENV['AZURE_OPENAI_GPT_API_VERSION'],
    $_ENV['AZURE_OPENAI_KEY'],
);
$model = new GPT(GPT::GPT_4O_MINI);

$chain = new Chain($platform, $model);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chain->call($messages);

echo $response->getContent().\PHP_EOL;
