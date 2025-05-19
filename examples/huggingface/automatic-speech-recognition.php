<?php

use PhpLlm\LlmChain\Bridge\HuggingFace\Model;
use PhpLlm\LlmChain\Bridge\HuggingFace\PlatformFactory;
use PhpLlm\LlmChain\Bridge\HuggingFace\Task;
use PhpLlm\LlmChain\Model\Message\Content\Audio;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['HUGGINGFACE_KEY'])) {
    echo 'Please set the HUGGINGFACE_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['HUGGINGFACE_KEY']);
$model = new Model('openai/whisper-large-v3');
$audio = Audio::fromFile(dirname(__DIR__, 2).'/tests/Fixture/audio.mp3');

$response = $platform->request($model, $audio, [
    'task' => Task::AUTOMATIC_SPEECH_RECOGNITION,
]);

echo $response->getContent().PHP_EOL;
