<?php

use MongoDB\Client as MongoDBClient;
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\SimilaritySearch;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Store\Bridge\MongoDB\Store;
use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Document\Vectorizer;
use PhpLlm\LlmChain\Store\Indexer;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Uid\Uuid;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (empty($_ENV['OPENAI_API_KEY']) || empty($_ENV['MONGODB_URI'])) {
    echo 'Please set OPENAI_API_KEY and MONGODB_URI environment variables.'.\PHP_EOL;
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
        content: 'Title: '.$movie['title'].\PHP_EOL.'Director: '.$movie['director'].\PHP_EOL.'Description: '.$movie['description'],
        metadata: new Metadata($movie),
    );
}

// create embeddings for documents
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$vectorizer = new Vectorizer($platform, $embeddings = new Embeddings());
$indexer = new Indexer($vectorizer, $store);
$indexer->index($documents);

// initialize the index
$store->initialize();

$model = new GPT(GPT::GPT_4O_MINI);

$similaritySearch = new SimilaritySearch($platform, $embeddings, $store);
$toolbox = Toolbox::create($similaritySearch);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $model, [$processor], [$processor]);

$messages = new MessageBag(
    Message::forSystem('Please answer all user questions only using SimilaritySearch function.'),
    Message::ofUser('Which movie fits the theme of the mafia?')
);
$response = $chain->call($messages);

echo $response->getContent().\PHP_EOL;
