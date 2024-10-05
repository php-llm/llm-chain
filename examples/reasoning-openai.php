<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

if (empty($_ENV['RUN_EXPENSIVE_EXAMPLES']) || false === filter_var($_ENV['RUN_EXPENSIVE_EXAMPLES'], FILTER_VALIDATE_BOOLEAN)) {
    echo 'This example is marked as expensive and will not run unless RUN_EXPENSIVE_EXAMPLES is set to true.'.PHP_EOL;
    exit(1);
}

$platform = new OpenAI(HttpClient::create(), $_ENV['OPENAI_API_KEY']);
$llm = new Gpt($platform, Version::o1Preview());

$prompt = <<<PROMPT
I want to build a Symfony app in PHP 8.2 that takes user questions and looks them
up in a database where they are mapped to answers. If there is close match, it
retrieves the matched answer. If there isn't, it asks the user to provide an answer
and stores the question/answer pair in the database. Make a plan for the directory 
structure you'll need, then return each file in full. Only supply your reasoning 
at the beginning and end, not throughout the code.
PROMPT;

$response = (new Chain($llm))->call(new MessageBag(Message::ofUser($prompt)));

echo $response->getContent().PHP_EOL;
