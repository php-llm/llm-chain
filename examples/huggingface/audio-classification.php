<?php

use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\PlatformFactory;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Task;
use PhpLlm\LlmChain\Platform\Message\Content\Audio;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (!$_ENV['HUGGINGFACE_KEY']) {
    echo 'Please set the HUGGINGFACE_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['HUGGINGFACE_KEY']);
$model = new Model('MIT/ast-finetuned-audioset-10-10-0.4593');
$audio = Audio::fromFile(dirname(__DIR__, 2).'/tests/Fixture/audio.mp3');

$response = $platform->request($model, $audio, [
    'task' => Task::AUDIO_CLASSIFICATION,
]);

dump($response->getContent());
