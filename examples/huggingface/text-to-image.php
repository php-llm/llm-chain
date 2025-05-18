<?php

use PhpLlm\LlmChain\Bridge\HuggingFace\PlatformFactory;
use PhpLlm\LlmChain\Bridge\HuggingFace\Task;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\BinaryResponse;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['HUGGINGFACE_KEY'])) {
    echo 'Please set the HUGGINGFACE_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['HUGGINGFACE_KEY']);
$model = new Model('black-forest-labs/FLUX.1-dev');

$response = $platform->request($model, 'Astronaut riding a horse', [
    'task' => Task::TEXT_TO_IMAGE,
]);

assert($response instanceof BinaryResponse);

echo $response->toBase64().PHP_EOL;
