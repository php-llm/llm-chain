<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Memory\EmbeddingProvider;
use PhpLlm\LlmChain\Chain\Memory\MemoryInputProcessor;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Store\Bridge\MariaDB\Store;
use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\TextDocument;
use PhpLlm\LlmChain\Store\Document\Vectorizer;
use PhpLlm\LlmChain\Store\Indexer;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Uid\Uuid;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__, 2).'/.env');

if (!$_ENV['OPENAI_API_KEY'] || !$_ENV['MARIADB_URI']) {
    echo 'Please set OPENAI_API_KEY and MARIADB_URI environment variables.'.\PHP_EOL;
    exit(1);
}

// initialize the store
$store = Store::fromDbal(
    connection: DriverManager::getConnection((new DsnParser())->parse($_ENV['MARIADB_URI'])),
    tableName: 'my_table',
    indexName: 'my_index',
    vectorFieldName: 'embedding',
);

// our data
$pastConversationPieces = [
    ['role' => 'user', 'timestamp' => '2024-12-14 12:00:00', 'content' => 'My friends John and Emma are friends, too, are there hints why?'],
    ['role' => 'assistant', 'timestamp' => '2024-12-14 12:00:01', 'content' => 'Based on the found documents i would expect they are friends since childhood, this can give a deep bound!'],
    ['role' => 'user', 'timestamp' => '2024-12-14 12:02:02', 'content' => 'Yeah but how does this bound? I know John was once there with a wound dressing as Emma fell, could this be a hint?'],
    ['role' => 'assistant', 'timestamp' => '2024-12-14 12:02:03', 'content' => 'Yes, this could be a hint that they have been through difficult times together, which can strengthen their bond.'],
];

// create embeddings and documents
foreach ($pastConversationPieces as $i => $message) {
    $documents[] = new TextDocument(
        id: Uuid::v4(),
        content: 'Role: '.$message['role'].\PHP_EOL.'Timestamp: '.$message['timestamp'].\PHP_EOL.'Message: '.$message['content'],
        metadata: new Metadata($message),
    );
}

// initialize the table
$store->initialize();

// create embeddings for documents as preparation of the chain memory
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$vectorizer = new Vectorizer($platform, $embeddings = new Embeddings());
$indexer = new Indexer($vectorizer, $store);
$indexer->index($documents);

// Execute a chat call that is utilizing the memory
$embeddingsMemory = new EmbeddingProvider($platform, $embeddings, $store);
$memoryProcessor = new MemoryInputProcessor($embeddingsMemory);

$chain = new Chain($platform, new GPT(GPT::GPT_4O_MINI), [$memoryProcessor]);
$messages = new MessageBag(Message::ofUser('Have we discussed about my friend John in the past? If yes, what did we talk about?'));
$response = $chain->call($messages);

echo $response->getContent().\PHP_EOL;
