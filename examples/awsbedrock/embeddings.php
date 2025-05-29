<?php

use PhpLlm\LlmChain\Bridge\AwsBedrock\Embeddings;
use PhpLlm\LlmChain\Bridge\AwsBedrock\PlatformFactory;
use PhpLlm\LlmChain\Document\Vector;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['AWS_ACCESS_KEY']) || empty($_ENV['AWS_ACCESS_SECRET']) || empty($_ENV['AWS_REGION'])) {
    echo 'Please set the AWS_ACCESS_KEY, AWS_ACCESS_SECRET, AWS_REGION environment variables.'.PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create(
    [
        'key' => $_ENV['AWS_ACCESS_KEY'],
        'secret' => $_ENV['AWS_ACCESS_SECRET'],
    ],
    $_ENV['AWS_REGION'],
);

$embeddings = new Embeddings();

$response = $platform->request($embeddings, <<<TEXT
    Once upon a time, there was a country called Japan. It was a beautiful country with a lot of mountains and rivers.
    The people of Japan were very kind and hardworking. They loved their country very much and took care of it. The
    country was very peaceful and prosperous. The people lived happily ever after.
    TEXT);

assert($response->getContent()[0] instanceof Vector);

echo 'Dimensions: '.$response->getContent()[0]->getDimensions().PHP_EOL;
