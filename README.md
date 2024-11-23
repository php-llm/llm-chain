# LLM Chain

PHP library for building LLM-based features and applications.

This library is not a stable yet, but still rather experimental. Feel free to try it out, give feedback, ask questions, contribute or share your use cases.
Abstractions, concepts and interfaces are not final and potentially subject of change.

## Requirements

* PHP 8.2 or higher

## Installation

The recommended way to install LLM Chain is through [Composer](http://getcomposer.org/):

```bash
composer require php-llm/llm-chain
```

When using Symfony Framework, check out the integration bundle [php-llm/llm-chain-bundle](https://github.com/php-llm/llm-chain-bundle).

## Examples

See [examples](examples) folder to run example implementations using this library.
Depending on the example you need to export different environment variables
for API keys or deployment configurations or create a `.env.local` based on `.env` file.

To run all examples, use `make run-all-examples` or `php example`.

## Basic Concepts & Usage

### Models & Platforms

LLM Chain categorizes two main types of models: **Language Models** and **Embeddings Models**.

Language Models, like GPT, Claude and Llama, as essential centerpiece of LLM applications
and Embeddings Models as supporting models to provide vector representations of text.

Those models are provided by different **platforms**, like OpenAI, Azure, Replicate, and others.

#### Example Instantiation

```php
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;

// Platform: OpenAI
$platform = PlatformFactory::create($_ENV['OPENAI_API_KEY']);

// Language Model: GPT (OpenAI)
$llm = new GPT(GPT::GPT_4O_MINI); 

// Embeddings Model: Embeddings (OpenAI)
$embeddings = new Embeddings();
```

#### Supported Models & Platforms

* Language Models
  * [OpenAI's GPT](https://platform.openai.com/docs/models/overview) with [OpenAI](https://platform.openai.com/docs/overview) and [Azure](https://learn.microsoft.com/azure/ai-services/openai/concepts/models) as Platform
  * [Anthropic's Claude](https://www.anthropic.com/claude) with [Anthropic](https://www.anthropic.com/) as Platform
  * [Meta's Llama](https://www.llama.com/) with [Ollama](https://ollama.com/) and [Replicate](https://replicate.com/) as Platform
* Embeddings Models
  * [OpenAI's Text Embeddings](https://platform.openai.com/docs/guides/embeddings/embedding-models) with [OpenAI](https://platform.openai.com/docs/overview) and [Azure](https://learn.microsoft.com/azure/ai-services/openai/concepts/models) as Platform
  * [Voyage's Embeddings](https://docs.voyageai.com/docs/embeddings) with [Voyage](https://www.voyageai.com/) as Platform

See [issue #28](https://github.com/php-llm/llm-chain/issues/28) for planned support of other models and platforms.

### Chain & Messages

The core feature of LLM Chain is to interact with language models via messages. This interaction is done by sending
a **MessageBag** to a **Chain**, which takes care of LLM invocation and response handling.

Messages can be of different types, most importantly `UserMessage`, `SystemMessage`, or `AssistantMessage`, and can also
have different content types, like `Text` or `Image`.

#### Example Chain call with messages

```php
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;

// Platform & LLM instantiation

$chain = new Chain($platform, $llm);
$messages = new MessageBag(
    new SystemMessage('You are a helpful chatbot answering questions about LLM Chain.'),
    new UserMessage('Hello, how are you?'),
);
$response = $chain->call($messages);

echo $response->getContent(); // "I'm fine, thank you. How can I help you today?"
```

The `MessageInterface` and `Content` interface help to customize this process if needed, e.g. additional state handling.

#### Code Examples

1. **Anthropic's Claude**: [chat-claude-anthropic.php](examples/chat-claude-anthropic.php)
1. **OpenAI's GPT with Azure**: [chat-gpt-azure.php](examples/chat-gpt-azure.php)
1. **OpenAI's GPT**: [chat-gpt-openai.php](examples/chat-gpt-openai.php)
1. **OpenAI's o1**: [chat-o1-openai.php](examples/chat-o1-openai.php)
1. **Meta's Llama with Ollama**: [chat-llama-ollama.php](examples/chat-llama-ollama.php)
1. **Meta's Llama with Replicate**: [chat-llama-replicate.php](examples/chat-llama-replicate.php)

### Tools

To integrate LLMs with your application, LLM Chain supports [tool calling](https://platform.openai.com/docs/guides/function-calling) out of the box.
Tools are services that can be called by the LLM to provide additional features or process data.

Tool calling can be enabled by registering the processors in the chain:
```php
use PhpLlm\LlmChain\Chain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\Chain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBox;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

// Platform & LLM instantiation

$yourTool = new YourTool();

$toolBox = new ToolBox(new ToolAnalyzer(), [$yourTool]);
$toolProcessor = new ChainProcessor($toolBox);

$chain = new Chain($platform, $llm, inputProcessor: [$toolProcessor], outputProcessor: [$toolProcessor]);
```

Custom tools can basically be any class, but must configure by the `#[AsTool]` attribute.

```php
use PhpLlm\LlmChain\ToolBox\Attribute\AsTool;

#[AsTool('company_name', 'Provides the name of your company')]
final class CompanyName
{
    public function __invoke(): string
    {
        return 'ACME Corp.'
    }
}
```

#### Code Examples (with built-in tools)

1. **Clock Tool**: [toolbox-clock.php](examples/toolbox-clock.php)
1. **SerpAPI Tool**: [toolbox-serpapi.php](examples/toolbox-serpapi.php)
1. **Weather Tool**: [toolbox-weather.php](examples/toolbox-weather.php)
1. **Wikipedia Tool**: [toolbox-wikipedia.php](examples/toolbox-wikipedia.php)
1. **YouTube Transcriber Tool**: [toolbox-youtube.php](examples/toolbox-youtube.php) (with streaming)

### Document Embedding, Vector Stores & Similarity Search (RAG)

LLM Chain supports document embedding and similarity search using vector stores like ChromaDB, Azure AI Search, MongoDB
Atlas Search, or Pinecone.

For populating a vector store, LLM Chain provides the service `DocumentEmbedder`, which requires an instance of an
`EmbeddingsModel` and one of `StoreInterface`, and works with a collection of `Document` objects as input:

```php
use PhpLlm\LlmChain\Embedder;
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Bridge\Pinecone\Store;
use Probots\Pinecone\Pinecone;
use Symfony\Component\HttpClient\HttpClient;

$embedder = new Embedder(
    PlatformFactory::create($_ENV['OPENAI_API_KEY']),
    new Embeddings(),
    new Store(Pinecone::client($_ENV['PINECONE_API_KEY'], $_ENV['PINECONE_HOST']),
);
$embedder->embed($documents);
```

The collection of `Document` instances is usually created by text input of your domain entities:

```php
use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\TextDocument;

foreach ($entities as $entity) {
    $documents[] = new TextDocument(
        id: $entity->getId(),                       // UUID instance
        content: $entity->toString(),               // Text representation of relevant data for embedding
        metadata: new Metadata($entity->toArray()), // Array representation of entity to be stored additionally
    );
}
```
> [!NOTE]
> Not all data needs to be stored in the vector store, but you could also hydrate the original data entry based
> on the ID or metadata after retrieval from the store.*

In the end the chain is used in combination with a retrieval tool on top of the vector store, e.g. the built-in
`SimilaritySearch` tool provided by the library:

```php
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Chain\ToolBox\ChainProcessor;
use PhpLlm\LlmChain\Chain\ToolBox\Tool\SimilaritySearch;
use PhpLlm\LlmChain\Chain\ToolBox\ToolAnalyzer;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBox;

// Initialize Platform & Models

$similaritySearch = new SimilaritySearch($embeddings, $store);
$toolBox = new ToolBox(new ToolAnalyzer(), [$similaritySearch]);
$processor = new ChainProcessor($toolBox);
$chain = new Chain($platform, $llm, [$processor], [$processor]);

$messages = new MessageBag(
    Message::forSystem(<<<PROMPT
        Please answer all user questions only using the similary_search tool. Do not add information and if you cannot
        find an answer, say so.
        PROMPT>>>),
    Message::ofUser('...') // The user's question.
);
$response = $chain->call($messages);
```

#### Code Examples

1. **MongoDB Store**: [store-mongodb-similarity-search.php](examples/store-mongodb-similarity-search.php)
1. **Pinecone Store**: [store-pinecone-similarity-search.php](examples/store-pinecone-similarity-search.php)

#### Supported Stores

* [ChromaDB](https://trychroma.com) (requires `codewithkyrian/chromadb-php` as additional dependency)
* [Azure AI Search](https://azure.microsoft.com/en-us/products/ai-services/ai-search)
* [MongoDB Atlas Search](https://mongodb.com/products/platform/atlas-vector-search) (requires `mongodb/mongodb` as additional dependency)
* [Pinecone](https://pinecone.io) (requires `probots-io/pinecone-php` as additional dependency)

See [issue #28](https://github.com/php-llm/llm-chain/issues/28) for planned support of other models and platforms. 

## Advanced Usage & Features

### Structured Output

A typical use-case of LLMs is to classify and extract data from unstructured sources, which is supported by some models
by features like **Structured Output** or providing a **Response Format**.

#### PHP Classes as Output

LLM Chain support that use-case by abstracting the hustle of defining and providing schemas to the LLM and converting
the response back to PHP objects.

To achieve this, a specific chain processor needs to be registered:
```php
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Chain\StructuredOutput\ChainProcessor;
use PhpLlm\LlmChain\Chain\StructuredOutput\ResponseFormatFactory;
use PhpLlm\LlmChain\Tests\Chain\StructuredOutput\Data\MathReasoning;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

// Initialize Platform and LLM

$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
$processor = new ChainProcessor(new ResponseFormatFactory(), $serializer);
$chain = new Chain($platform, $llm, [$processor], [$processor]);

$messages = new MessageBag(
    Message::forSystem('You are a helpful math tutor. Guide the user through the solution step by step.'),
    Message::ofUser('how can I solve 8x + 7 = -23'),
);
$response = $chain->call($messages, ['output_structure' => MathReasoning::class]);

dump($response->getContent()); // returns an instance of `MathReasoning` class
```

#### Array Structures as Output

Also PHP array structures as `response_format` are supported, which also requires the chain processor mentioned above:

```php
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;

// Initialize Platform, LLM and Chain with processors and Clock tool

$messages = new MessageBag(Message::ofUser('What date and time is it?'));
$response = $chain->call($messages, ['response_format' => [
    'type' => 'json_schema',
    'json_schema' => [
        'name' => 'clock',
        'strict' => true,
        'schema' => [
            'type' => 'object',
            'properties' => [
                'date' => ['type' => 'string', 'description' => 'The current date in the format YYYY-MM-DD.'],
                'time' => ['type' => 'string', 'description' => 'The current time in the format HH:MM:SS.'],
            ],
            'required' => ['date', 'time'],
            'additionalProperties' => false,
        ],
    ],
]]);

dump($response->getContent()); // returns an array
```

#### Code Examples

1. **Structured Output** (PHP class): [structured-output-math.php](examples/structured-output-math.php)
1. **Structured Output** (array): [structured-output-clock.php](examples/structured-output-clock.php)

### Tool Parameters

LLM Chain generates a JSON Schema representation for all tools in the `ToolBox` based on the `#[AsTool]` attribute and
method arguments and doc block. Additionally, JSON Schema support validation rules, which are partially support by
LLMs like GPT.

To leverage this, configure the `#[ToolParameter]` attribute on the method arguments of your tool:
```php
use PhpLlm\LlmChain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\ToolBox\Attribute\ToolParameter;

#[AsTool('my_tool', 'Example tool with parameters requirements.')]
final class MyTool
{
    /**
     * @param string $name   The name of an object
     * @param int    $number The number of an object
     */
    public function __invoke(
        #[ToolParameter(pattern: '/([a-z0-1]){5}/')]
        string $name,
        #[ToolParameter(minimum: 0, maximum: 10)]   
        int $number,
    ): string {
        // ...
    }
}
```
> [!NOTE]
> Please be aware, that this is only converted in a JSON Schema for the LLM to respect, but not validated by LLM Chain.

### Response Streaming

Since LLMs usually generate a response word by word, most of them also support streaming the response using Server Side
Events. LLM Chain supports that by abstracting the conversion and returning a Generator as content of the response.

```php
use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;

// Initialize Platform and LLM

$chain = new Chain($llm);
$messages = new MessageBag(
    Message::forSystem('You are a thoughtful philosopher.'),
    Message::ofUser('What is the purpose of an ant?'),
);
$response = $chain->call($messages, [
    'stream' => true, // enable streaming of response text
]);

foreach ($response->getContent() as $word) {
    echo $word;
}
```

In a terminal application this generator can be used directly, but with a web app an additional layer like [Mercure](https://mercure.rocks)
needs to be used.

#### Code Examples

1. **Streaming Claude**: [stream-claude-anthropic.php](examples/stream-claude-anthropic.php)
1. **Streaming GPT**: [stream-gpt-openai.php](examples/stream-gpt-openai.php)

### Image Processing

Some LLMs also support images as input, which LLM Chain supports as `Content` type within the `UserMessage`:

```php
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;

// Initialize Platoform, LLM & Chain

$messages = new MessageBag(
    Message::forSystem('You are an image analyzer bot that helps identify the content of images.'),
    Message::ofUser(
        'Describe the image as a comedian would do it.',
        new Image(dirname(__DIR__).'/tests/Fixture/image.png'), // Path to an image file
        new Image('https://foo.com/bar.png'), // URL to an image
        new Image('data:image/png;base64,...'), // Data URL of an image
    ),
);
$response = $chain->call($messages);
```

#### Code Examples

1. **Image Description**: [image-describer-binary.php](examples/image-describer-binary.php) (with binary file)
1. **Image Description**: [image-describer-url.php](examples/image-describer-url.php) (with URL)

### Embeddings

Creating embeddings of word, sentences or paragraphs is a typical use case around the interaction with LLMs and
therefore LLM Chain implements a `EmbeddingsModel` interface with various models, see above.

The standalone usage results in an `Vector` instance:

```php
use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;

// Initialize Platform

$embeddings = new Embeddings($platform, Embeddings::TEXT_3_SMALL);

$vectors = $platform->request($embeddings, $textInput)->getContent();

dump($vectors[0]->getData()); // Array of float values
```

#### Code Examples

1. **OpenAI's Emebddings**: [embeddings-openai.php](examples/embeddings-openai.php)
1. **Voyage's Embeddings**: [embeddings-voyage.php](examples/embeddings-voyage.php)

### Input & Output Processing

The behavior of the Chain is extendable with services that implement `InputProcessor` and/or `OutputProcessor`
interface. They are provided while instantiating the Chain instance:

```php
use PhpLlm\LlmChain\Chain;

// Initialize Platform, LLM and processors

$chain = new Chain($platform, $llm, $inputProcessors, $outputProcessors);
```

#### InputProcessor

`InputProcessor` instances are called in the chain before handing over the `MessageBag` and the `$options` array to the LLM and are
able to mutate both on top of the `Input` instance provided.

```php
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Model\Message\AssistantMessage

final class MyProcessor implements InputProcessor
{
    public function processInput(Input $input): void
    {
        // mutate options
        $options = $input->getOptions();
        $options['foo'] = 'bar';
        $input->setOptions($options);
        
        // mutate MessageBag
        $input->messages->append(new AssistantMessage(sprintf('Please answer using the locale %s', $this->locale)));
    }
}
```

#### OutputProcessor

`OutputProcessor` instances are called after the LLM provided a response and can - on top of options and messages -
mutate or replace the given response:

```php
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Model\Message\AssistantMessage

final class MyProcessor implements OutputProcessor
{
    public function processOutput(Output $out): void
    {
        // mutate response
        if (str_contains($output->response->getContent, self::STOP_WORD)) {
            $output->reponse = new TextReponse('Sorry, we were unable to find relevant information.')
        }
    }
}
```

#### Chain Awareness

Both, `Input` and `Output` instances, provide access to the LLM used by the Chain, but the chain itself is only
provided, in case the processor implemented the `ChainAwareProcessor` interface, which can be combined with using the
`ChainAwareTrait`:

```php
use PhpLlm\LlmChain\Chain\ChainAwareProcessor;
use PhpLlm\LlmChain\Chain\ChainAwareTrait;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Model\Message\AssistantMessage

final class MyProcessor implements OutputProcessor, ChainAwareProcessor
{
    use ChainAwareTrait;

    public function processOutput(Output $out): void
    {
        // additional chain interaction
        $response = $this->chain->call(...);
    }
}
```

## Contributions

Contributions are always welcome, so feel free to join the development of this library.

### Current Contributors

[![LLM Chain Contributors](https://contrib.rocks/image?repo=php-llm/llm-chain 'LLM Chain Contributors')](https://github.com/php-llm/llm-chain/graphs/contributors)

Made with [contrib.rocks](https://contrib.rocks).
