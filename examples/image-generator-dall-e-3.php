<?php

use PhpLlm\LlmChain\Bridge\OpenAI\DallE;
use PhpLlm\LlmChain\Bridge\OpenAI\DallE\ImageResponse;
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

$response = $platform->request(
    model: new DallE(),
    input: 'A cartoon-style elephant with a long trunk and large ears.',
    options: [
        'version' => DallE::DALL_E_3, // Utilize Dall-E 3 version
    ],
);

if ($response instanceof AsyncResponse) {
    $response = $response->unwrap();
}

assert($response instanceof ImageResponse);

echo 'Revised Prompt: '.$response->revisedPrompt.PHP_EOL.PHP_EOL;

foreach ($response->getContent() as $index => $image) {
    echo 'Image '.$index.': '.$image->url.PHP_EOL;
}
