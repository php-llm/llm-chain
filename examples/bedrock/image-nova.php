<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Nova;
use PhpLlm\LlmChain\Platform\Bridge\Bedrock\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Content\Image;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['AWS_ACCESS_KEY_ID']) || empty($_ENV['AWS_SECRET_ACCESS_KEY']) || empty($_ENV['AWS_DEFAULT_REGION'])
) {
    echo 'Please set the AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY and AWS_DEFAULT_REGION environment variables.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create();
$model = new Nova(Nova::PRO);

$chain = new Chain($platform, $model);
$messages = new MessageBag(
    Message::forSystem('You are an image analyzer bot that helps identify the content of images.'),
    Message::ofUser(
        'Describe the image as a comedian would do it.',
        Image::fromFile(dirname(__DIR__, 2).'/tests/Fixture/image.jpg'),
    ),
);
$response = $chain->call($messages);

echo $response->getContent().\PHP_EOL;
