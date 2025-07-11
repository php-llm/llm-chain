<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\InputProcessor\SystemPromptInputProcessor;
use PhpLlm\LlmChain\Chain\Memory\MemoryInputProcessor;
use PhpLlm\LlmChain\Chain\Memory\StaticMemoryProvider;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (!$_ENV['OPENAI_API_KEY']) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new GPT(GPT::GPT_4O_MINI);

$systemPromptProcessor = new SystemPromptInputProcessor('You are a professional trainer with short, personalized advices and a motivating claim.');

$personalFacts = new StaticMemoryProvider(
    'My name is Wilhelm Tell',
    'I wish to be a swiss national hero',
    'I am struggling with hitting apples but want to be professional with the bow and arrow',
);
$memoryProcessor = new MemoryInputProcessor($personalFacts);

$chain = new Chain($platform, $model, [$systemPromptProcessor, $memoryProcessor]);
$messages = new MessageBag(Message::ofUser('What do we do today?'));
$response = $chain->call($messages);

echo $response->getContent().\PHP_EOL;
