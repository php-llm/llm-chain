<?php

use PhpLlm\LlmChain\Bridge\OpenAI\DallE;
use PhpLlm\LlmChain\Bridge\OpenAI\DallE\ImagesResponse;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$llm = new DallE(DallE::DALL_E_3);

$response = $platform->request(
    model: $llm,
    input: 'A cartoon-style elephant with a long trunk and large ears.',
    options: ['response_format' => 'url', 'n' => 1],
);

if ($response instanceof AsyncResponse) {
    $response = $response->unwrap();
}

assert($response instanceof ImagesResponse);

echo 'Revised Prompt: '.$response->revisedPrompt.PHP_EOL.PHP_EOL;

foreach ($response->getContent() as $index => $image) {
    echo 'Image '.$index.': '.$image->url.PHP_EOL;
}
