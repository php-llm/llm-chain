<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use PhpLlm\LlmChain\Bridge\MySQL\Store;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\SimilaritySearch;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\TextDocument;
use PhpLlm\LlmChain\Embedder;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

// Establish MySQL connection
$dsn = $_ENV['MYSQL_DSN'] ?? 'mysql:host=localhost;port=3306;dbname=llm_chain;charset=utf8mb4';
$username = $_ENV['MYSQL_USERNAME'] ?? 'root';
$password = $_ENV['MYSQL_PASSWORD'] ?? 'password';
$pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Initialize Platform & Models
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);
$embeddingsModel = new Embeddings($platform);
$llm = new GPT(GPT::GPT_4O_MINI);

// Initialize Store
$store = new Store(
    $pdo, 
    'vector_documents', // Table name
    'vector_data',      // Vector column name
    'metadata',         // Metadata column name
    [],                 // Additional options
    1536,               // Vector dimensions for OpenAI Embeddings
    3                   // Default limit for results
);

// Create demo data for the store
$examples = [
    [
        'question' => 'What is PHP?',
        'answer' => 'PHP (recursive acronym and backronym for "PHP: Hypertext Preprocessor", originally "Personal Home Page Tools") is a scripting language with a syntax similar to C and Perl, mainly used for creating dynamic web pages.',
    ],
    [
        'question' => 'What is MySQL?',
        'answer' => 'MySQL is a relational open-source database management system that uses SQL as its query language. MySQL version 9 provides native vector support for AI applications, enabling efficient similarity search operations.',
    ],
    [
        'question' => 'What is LLM Chain?',
        'answer' => 'LLM Chain is a PHP library for developing LLM-based and AI-based features and applications. It supports various language models, platforms, and vector stores for building intelligent applications.',
    ],
    [
        'question' => 'What is a vector store?',
        'answer' => 'A vector store is a specialized type of database optimized for storing and querying vector data, typically derived from embedding models. They enable similarity searches based on the semantic meaning of texts, making them ideal for RAG (Retrieval-Augmented Generation) systems.',
    ],
    [
        'question' => 'How does MySQL 9 vector support work?',
        'answer' => 'MySQL 9 introduces native vector support through the VECTOR data type and specialized functions like VECTOR_COSINE_DISTANCE. This enables efficient storage and similarity search operations directly within the database, simplifying the architecture for AI applications by eliminating the need for separate vector database services.',
    ],
];

// Insert data into the store
$embedder = new Embedder($platform, $embeddingsModel, $store);
$documents = [];

echo "Creating and storing embeddings for the example data...\n";
foreach ($examples as $index => $example) {
    $content = sprintf("Question: %s\nAnswer: %s", $example['question'], $example['answer']);
    $documents[] = new TextDocument(
        id: Symfony\Component\Uid\Uuid::v4(),
        content: $content,
        metadata: new Metadata(['index' => $index, 'question' => $example['question']]),
    );
}

$embedder->embed($documents);
echo "Embeddings successfully stored.\n\n";

// Create Chain with SimilaritySearch tool
$similaritySearch = new SimilaritySearch($embeddingsModel, $store);
$toolbox = Toolbox::create($similaritySearch);
$processor = new ChainProcessor($toolbox);
$chain = new Chain($platform, $llm, [$processor], [$processor]);

// Simulate user request
$userQuestion = 'Explain what a vector store is and how it works with MySQL 9.';

echo "User query: {$userQuestion}\n";
$messages = new MessageBag(
    Message::forSystem(<<<PROMPT
You are a helpful assistant that only uses the provided information to answer questions.
Use only the similarity_search tool to answer.
If you cannot find relevant information, honestly say so and don't make up an answer.
PROMPT
    ),
    Message::ofUser($userQuestion),
);

// Call chain and output response
$response = $chain->call($messages);
echo "Answer: {$response->getContent()}\n";