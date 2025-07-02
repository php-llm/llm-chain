<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\Mistral;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (!$_ENV['MISTRAL_API_KEY']) {
    echo 'Please set the REPLICATE_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['MISTRAL_API_KEY']);
$model = new Mistral();
$chain = new Chain($platform, $model);

$messages = new MessageBag(Message::ofUser('What is the eighth prime number?'));
$response = $chain->call($messages, [
    'stream' => true,
]);

foreach ($response->getContent() as $word) {
    echo $word;
}
echo \PHP_EOL;
