<?php

declare(strict_types=1);

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (!$_ENV['OPENAI_API_KEY']) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$ada = new Embeddings(Embeddings::TEXT_ADA_002);
$small = new Embeddings(Embeddings::TEXT_3_SMALL);
$large = new Embeddings(Embeddings::TEXT_3_LARGE);

echo 'Initiating parallel embeddings calls to platform ...'.\PHP_EOL;
$responses = [];
foreach (['ADA' => $ada, 'Small' => $small, 'Large' => $large] as $name => $model) {
    echo ' - Request for model '.$name.' initiated.'.\PHP_EOL;
    $responses[] = $platform->request($model, 'Hello, world!');
}

echo 'Waiting for the responses ...'.\PHP_EOL;
foreach ($responses as $response) {
    echo 'Dimensions: '.$response->asVectors()[0]->getDimensions().\PHP_EOL;
}
