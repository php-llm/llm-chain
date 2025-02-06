<?php

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chat;
use PhpLlm\LlmChain\Chat\MessageStore\InMemoryStore;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new GPT(GPT::GPT_4O_MINI);

$chain = new Chain($platform, $llm);
$chat = new Chat($chain, new InMemoryStore());

$messages = new MessageBag(
    Message::forSystem('You are a helpful assistant. You only answer with short sentences.'),
);

$chat->start($messages);
$chat->submit(Message::ofUser('My name is Christopher.'));
$message = $chat->submit(Message::ofUser('What is my name?'));

echo $message->content.PHP_EOL;
