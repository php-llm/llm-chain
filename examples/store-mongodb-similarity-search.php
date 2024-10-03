<?php

use MongoDB\Client as MongoDBClient;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\TextDocument;
use PhpLlm\LlmChain\DocumentEmbedder;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Embeddings;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use PhpLlm\LlmChain\Store\MongoDB\Store;
use PhpLlm\LlmChain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\ToolBox\Tool\SimilaritySearch;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolBox\ToolBox;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY']) || empty($_ENV['MONGODB_URI'])) {
    echo 'Please set OPENAI_API_KEY and MONGODB_URI environment variables.'.PHP_EOL;
    exit(1);
}

// initialize the store
$store = new Store(
    client: new MongoDBClient($_ENV['MONGODB_URI']),
    databaseName: 'my-database',
    collectionName: 'my-collection',
    indexName: 'my-index',
    vectorFieldName: 'vector',
);

// our data
$movies = [
    ['title' => 'Inception', 'description' => 'A skilled thief is given a chance at redemption if he can successfully perform inception, the act of planting an idea in someone\'s subconscious.', 'director' => 'Christopher Nolan'],
    ['title' => 'The Matrix', 'description' => 'A hacker discovers the world he lives in is a simulated reality and joins a rebellion to overthrow its controllers.', 'director' => 'The Wachowskis'],
    ['title' => 'The Godfather', 'description' => 'The aging patriarch of an organized crime dynasty transfers control of his empire to his reluctant son.', 'director' => 'Francis Ford Coppola'],
];

// create embeddings and documents
foreach ($movies as $movie) {
    $documents[] = new TextDocument(
        id: Uuid::v4(),
        content: 'Title: '.$movie['title'].PHP_EOL.'Director: '.$movie['director'].PHP_EOL.'Description: '.$movie['description'],
        metadata: new Metadata($movie),
    );
}

// create embeddings for documents
$platform = new OpenAI(HttpClient::create(), $_ENV['OPENAI_API_KEY']);
$embedder = new DocumentEmbedder($embeddings = new Embeddings($platform), $store);
$embedder->embed($documents);

// initialize the index
$store->initialize();

$llm = new Gpt($platform, Version::gpt4oMini());

$similaritySearch = new SimilaritySearch($embeddings, $store);
$toolBox = new ToolBox(new ToolAnalyzer(), [$similaritySearch]);
$processor = new ChainProcessor($toolBox);
$chain = new Chain($llm, [$processor], [$processor]);

$messages = new MessageBag(
    Message::forSystem('Please answer all user questions only using SimilaritySearch function.'),
    Message::ofUser('Which movie fits the theme of the mafia?')
);
$response = $chain->call($messages);

echo $response.PHP_EOL;
