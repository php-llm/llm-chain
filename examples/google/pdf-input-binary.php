<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Bridge\Google\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Content\Document;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['GEMINI_API_KEY'])) {
    echo 'Please set the GEMINI_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['GEMINI_API_KEY']);
$model = new Gemini(Gemini::GEMINI_1_5_FLASH);

$chain = new Chain($platform, $model);
$messages = new MessageBag(
    Message::ofUser(
        Document::fromFile(dirname(__DIR__, 2).'/tests/Fixture/document.pdf'),
        'What is this document about?',
    ),
);
$response = $chain->call($messages);

echo $response->getContent().\PHP_EOL;
