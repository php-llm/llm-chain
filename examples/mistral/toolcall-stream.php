<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\YouTubeTranscriber;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\Mistral;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (!$_ENV['MISTRAL_API_KEY']) {
    echo 'Please set the REPLICATE_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['MISTRAL_API_KEY']);
$model = new Mistral();

$transcriber = new YouTubeTranscriber(HttpClient::create());
$toolbox = Toolbox::create($transcriber);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $model, [$processor], [$processor]);

$messages = new MessageBag(Message::ofUser('Please summarize this video for me: https://www.youtube.com/watch?v=6uXW-ulpj0s'));
$response = $chain->call($messages, [
    'stream' => true,
]);

foreach ($response->getContent() as $word) {
    echo $word;
}
echo \PHP_EOL;
