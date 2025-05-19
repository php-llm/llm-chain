<?php

use PhpLlm\LlmChain\Bridge\HuggingFace\Model;
use PhpLlm\LlmChain\Bridge\HuggingFace\PlatformFactory;
use PhpLlm\LlmChain\Bridge\HuggingFace\Task;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['HUGGINGFACE_KEY'])) {
    echo 'Please set the HUGGINGFACE_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['HUGGINGFACE_KEY']);
$model = new Model('facebook/detr-resnet-50');

$image = Image::fromFile(dirname(__DIR__, 2).'/tests/Fixture/image.jpg');
$response = $platform->request($model, $image, [
    'task' => Task::OBJECT_DETECTION,
]);

dump($response->getContent());
