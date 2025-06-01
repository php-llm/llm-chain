<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Platform\Bridge\Azure\Meta\PlatformFactory;
use PhpLlm\LlmChain\Platform\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['AZURE_LLAMA_BASEURL']) || empty($_ENV['AZURE_LLAMA_KEY'])) {
    echo 'Please set the AZURE_LLAMA_BASEURL and AZURE_LLAMA_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['AZURE_LLAMA_BASEURL'], $_ENV['AZURE_LLAMA_KEY']);
$model = new Llama(Llama::V3_3_70B_INSTRUCT);

$chain = new Chain($platform, $model);
$messages = new MessageBag(Message::ofUser('I am going to Paris, what should I see?'));
$response = $chain->call($messages, [
    'max_tokens' => 2048,
    'temperature' => 0.8,
    'top_p' => 0.1,
    'presence_penalty' => 0,
    'frequency_penalty' => 0,
]);

echo $response->getContent().\PHP_EOL;
