<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\Message\UserMessage;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\EventSourceHttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = new OpenAI(new EventSourceHttpClient(), $_ENV['OPENAI_API_KEY']);
$llm = new Gpt($platform, Version::gpt4oMini());

$chain = new Chain($llm);
$messages = new MessageBag(
    new SystemMessage('You are a thoughtful philosopher.'),
    new UserMessage('What is the purpose of an ant?'),
);
$response = $chain->call($messages, [
    'stream' => true, // enable streaming of response text
]);

foreach ($response->getContent() as $word) {
    echo $word;
}
echo PHP_EOL;
