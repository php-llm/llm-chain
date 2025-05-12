<?php

use PhpLlm\LlmChain\Bridge\HuggingFace\PlatformFactory;
use PhpLlm\LlmChain\Bridge\HuggingFace\Task;
use PhpLlm\LlmChain\Model\Model;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['HUGGINGFACE_KEY'])) {
    echo 'Please set the HUGGINGFACE_KEY environment variable.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['HUGGINGFACE_KEY']);
$model = new Model('dbmdz/bert-large-cased-finetuned-conll03-english');

$response = $platform->request($model, 'John Smith works at Microsoft in London.', [
    'task' => Task::TOKEN_CLASSIFICATION,
]);

dump($response->getContent());
