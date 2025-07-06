<?php

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\StructuredOutput\ChainProcessor;
use PhpLlm\LlmChain\Chain\StructuredOutput\ResponseFormatFactory;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\Mistral;
use PhpLlm\LlmChain\Platform\Bridge\Mistral\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Tests\Fixture\StructuredOutput\MathReasoning;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (!$_ENV['MISTRAL_API_KEY']) {
    echo 'Please set the MISTRAL_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['MISTRAL_API_KEY']);
$model = new Mistral(Mistral::MISTRAL_SMALL);
$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

$processor = new ChainProcessor(new ResponseFormatFactory(), $serializer);
$chain = new Chain($platform, $model, [$processor], [$processor]);
$messages = new MessageBag(
    Message::forSystem('You are a helpful math tutor. Guide the user through the solution step by step.'),
    Message::ofUser('how can I solve 8x + 7 = -23'),
);
$response = $chain->call($messages, ['output_structure' => MathReasoning::class]);

dump($response->getContent());
