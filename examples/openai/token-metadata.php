<?php

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Bridge\OpenAI\TokenOutputProcessor;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new GPT(GPT::GPT_4O_MINI, [
    'temperature' => 0.5, // default options for the model
]);

$chain = new Chain($platform, $model, outputProcessors: [new TokenOutputProcessor()]);
$messages = new MessageBag(
    Message::forSystem('You are a pirate and you write funny.'),
    Message::ofUser('What is the Symfony framework?'),
);
$response = $chain->call($messages, [
    'max_tokens' => 500, // specific options just for this call
]);

$metadata = $response->getMetadata();

echo 'Utilized Tokens: '.$metadata['total_tokens'].PHP_EOL;
echo '-- Prompt Tokens: '.$metadata['prompt_tokens'].PHP_EOL;
echo '-- Completion Tokens: '.$metadata['completion_tokens'].PHP_EOL;
echo 'Remaining Tokens: '.$metadata['remaining_tokens'].PHP_EOL;
