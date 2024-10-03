<?php

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Document\Document;
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\DocumentEmbedder;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\Model\Embeddings;
use PhpLlm\LlmChain\OpenAI\Model\Gpt;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version;
use PhpLlm\LlmChain\OpenAI\Platform\OpenAI;
use PhpLlm\LlmChain\Store\Pinecone\Store;
use PhpLlm\LlmChain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\ToolBox\Tool\SimilaritySearch;
use PhpLlm\LlmChain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\ToolBox\ToolBox;
use Probots\Pinecone\Pinecone;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Uid\Uuid;

require_once dirname(__DIR__).'/vendor/autoload.php';
(new Dotenv())->loadEnv(dirname(__DIR__).'/.env');

if (empty($_ENV['OPENAI_API_KEY']) || empty($_ENV['PINECONE_API_KEY']) || empty($_ENV['PINECONE_HOST'])) {
    echo 'Please set OPENAI_API_KEY, PINECONE_API_KEY and PINECONE_HOST environment variables.'.PHP_EOL;
    exit(1);
}

// initialize the store
$store = new Store(Pinecone::client($_ENV['PINECONE_API_KEY'], $_ENV['PINECONE_HOST']));

// our data
$movies = [
    ['title' => 'Inception', 'description' => 'A skilled thief is given a chance at redemption if he can successfully perform inception, the act of planting an idea in someone\'s subconscious.', 'director' => 'Christopher Nolan'],
    ['title' => 'The Matrix', 'description' => 'A hacker discovers the world he lives in is a simulated reality and joins a rebellion to overthrow its controllers.', 'director' => 'The Wachowskis'],
    ['title' => 'The Godfather', 'description' => 'The aging patriarch of an organized crime dynasty transfers control of his empire to his reluctant son.', 'director' => 'Francis Ford Coppola'],
];

// create embeddings and documents
foreach ($movies as $movie) {
    $documents[] = Document::fromText(
        id: Uuid::v4(),
        text: 'Title: '.$movie['title'].PHP_EOL.'Director: '.$movie['director'].PHP_EOL.'Description: '.$movie['description'],
        metadata: new Metadata($movie),
    );
}

// create embeddings for documents
$platform = new OpenAI(HttpClient::create(), $_ENV['OPENAI_API_KEY']);
$embedder = new DocumentEmbedder($embeddings = new Embeddings($platform), $store);
$embedder->embed($documents);

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
