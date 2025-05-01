<?php

use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Model\Message\Content\Audio;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new Whisper();
$file = Audio::fromFile(dirname(__DIR__, 2).'/tests/Fixture/audio.mp3');

$response = $platform->request($model, $file);

echo $response->getContent().PHP_EOL;
