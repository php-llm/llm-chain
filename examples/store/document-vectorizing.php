<?php

declare(strict_types=1);

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Document\VectorDocument;
use PhpLlm\LlmChain\Store\Document\Vectorizer;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Uid\Uuid;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (!$_ENV['OPENAI_API_KEY']) {
    echo 'Please set the OPENAI_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$embeddings = new Embeddings(Embeddings::TEXT_3_LARGE);

$textDocuments = [
    new TextDocument(Uuid::v4(), 'Hello World'),
    new TextDocument(Uuid::v4(), 'Lorem ipsum dolor sit amet'),
    new TextDocument(Uuid::v4(), 'PHP Hypertext Preprocessor'),
];

$vectorizer = new Vectorizer($platform, $embeddings);
$vectorDocuments = $vectorizer->vectorizeDocuments($textDocuments);

dump(array_map(fn (VectorDocument $document) => $document->vector->getDimensions(), $vectorDocuments));
