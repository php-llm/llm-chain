<?php

declare(strict_types=1);

use PhpLlm\FabricPattern\Pattern;
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

require_once dirname(__DIR__).'/../vendor/autoload.php';

if (empty($_ENV['OPENAI_API_KEY'])) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

if (!class_exists(Pattern::class)) {
    echo 'Fabric patterns are not installed. Please install them with: composer require php-llm/fabric-pattern'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$model = new GPT(GPT::GPT_4O_MINI);
$chain = new Chain($platform, $model);

$article = <<<'ARTICLE'
    The field of artificial intelligence has undergone dramatic transformations in recent years,
    with large language models (LLMs) emerging as one of the most significant breakthroughs.
    These models, trained on vast amounts of text data, have demonstrated remarkable capabilities
    in understanding and generating human-like text. The implications for software development,
    content creation, and human-computer interaction are profound.

    However, with these advances come important considerations regarding ethics, bias, and the
    responsible deployment of AI systems. Researchers and practitioners must work together to
    ensure that these powerful tools are used in ways that benefit society while minimizing
    potential harms.
    ARTICLE;

$messages = new MessageBag(
    Message::fabric('create_summary'),
    Message::ofUser($article)
);

$response = $chain->call($messages);

echo 'Summary using Fabric pattern "create_summary":'.\PHP_EOL;
echo '=============================================='.\PHP_EOL;
echo $response->getContent().\PHP_EOL;
