<?php

declare(strict_types=1);

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new GPT(GPT::GPT_4O_MINI, [
    'temperature' => 0.5, // default options for the model
]);

$messages = new MessageBag(
    Message::forSystem('You will be given a letter and you answer with only the next letter of the alphabet.'),
);

echo 'Initiating parallel calls to GPT on platform ...'.\PHP_EOL;
$responses = [];
foreach (range('A', 'D') as $letter) {
    echo ' - Request for the letter '.$letter.' initiated.'.\PHP_EOL;
    $responses[] = $platform->request($model, $messages->with(Message::ofUser($letter)));
}

echo 'Waiting for the responses ...'.\PHP_EOL;
foreach ($responses as $response) {
    echo 'Next Letter: '.$response->asText().\PHP_EOL;
}
