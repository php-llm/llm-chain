<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Wikipedia;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new GPT(GPT::GPT_4O_MINI);

$wikipedia = new Wikipedia(HttpClient::create());
$toolbox = Toolbox::create($wikipedia);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $model, [$processor], [$processor]);
$messages = new MessageBag(Message::ofUser(<<<TXT
        First, define unicorn in 30 words.
        Then lookup at Wikipedia what the irish history looks like in 2 sentences.
        Please tell me before you call tools.
    TXT));
$response = $chain->call($messages, [
    'stream' => true, // enable streaming of response text
]);

foreach ($response->getContent() as $word) {
    echo $word;
}

echo \PHP_EOL;
