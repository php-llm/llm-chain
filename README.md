# LLM Chain

> [!IMPORTANT]  
> **PHP LLM becomes Symfony AI** - this project moved to [github.com/symfony/ai](https://github.com/symfony/ai).
> Please use the new repository for all future development, issues, and contributions.
> Thanks for your contributions - we hope to see you at Symfony AI!

PHP library for building LLM-based and AI-based features and applications.

This library is not stable yet, but still rather experimental. Feel free to try it out, give feedback, ask questions,
contribute, or share your use cases. Abstractions, concepts, and interfaces are not final and potentially subject of change.

## Requirements

* PHP 8.2 or higher

## Installation

The recommended way to install LLM Chain is through [Composer](http://getcomposer.org/):

```bash
composer require php-llm/llm-chain
```

When using Symfony Framework, check out the integration bundle [php-llm/llm-chain-bundle](https://github.com/php-llm/llm-chain-bundle).

## Examples

See [the examples folder](examples) to run example implementations using this library.
Depending on the example you need to export different environment variables
for API keys or deployment configurations or create a `.env.local` based on `.env` file.

To run all examples, use `make run-examples` or `php example` - to run a subgroup like all HuggingFace related examples
use `php example huggingface`.

For a more sophisticated demo, see the [Symfony Demo Application](https://github.com/php-llm/symfony-demo).

## Basic Concepts & Usage

### Models & Platforms

LLM Chain categorizes two main types of models: **Language Models** and **Embeddings Models**. On top of that, there are
other models, like text-to-speech, image generation, or classification models that are also supported.

Language Models, like GPT, Claude, and Llama, as essential centerpiece of LLM applications
and Embeddings Models as supporting models to provide vector representations of a text.

Those models are provided by different **platforms**, like OpenAI, Azure, Google, Replicate, and others.

#### Example Instantiation

```php
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;

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
  * [Anthropic's Claude](https://www.anthropic.com/claude) with [Anthropic](https://www.anthropic.com/) and [AWS](https://aws.amazon.com/bedrock/) as Platform
  * [Meta's Llama](https://www.llama.com/) with [Azure](https://learn.microsoft.com/azure/machine-learning/how-to-deploy-models-llama), [Ollama](https://ollama.com/), [Replicate](https://replicate.com/) and [AWS](https://aws.amazon.com/bedrock/) as Platform
  * [Google's Gemini](https://gemini.google.com/) with [Google](https://ai.google.dev/) and [OpenRouter](https://www.openrouter.com/) as Platform
  * [DeepSeek's R1](https://www.deepseek.com/) with [OpenRouter](https://www.openrouter.com/) as Platform
  * [Amazon's Nova](https://nova.amazon.com) with [AWS](https://aws.amazon.com/bedrock/) as Platform
  * [Mistral's Mistral](https://www.mistral.ai/) with [Mistral](https://www.mistral.ai/) as Platform
  * [Albert API](https://github.com/etalab-ia/albert-api) models with [Albert](https://github.com/etalab-ia/albert-api) as Platform (French government's sovereign AI gateway)
* Embeddings Models
  * [OpenAI's Text Embeddings](https://platform.openai.com/docs/guides/embeddings/embedding-models) with [OpenAI](https://platform.openai.com/docs/overview) and [Azure](https://learn.microsoft.com/azure/ai-services/openai/concepts/models) as Platform
  * [Voyage's Embeddings](https://docs.voyageai.com/docs/embeddings) with [Voyage](https://www.voyageai.com/) as Platform
  * [Mistral Embed](https://www.mistral.ai/) with [Mistral](https://www.mistral.ai/) as Platform
* Other Models
  * [OpenAI's Dall·E](https://platform.openai.com/docs/guides/image-generation) with [OpenAI](https://platform.openai.com/docs/overview) as Platform
  * [OpenAI's Whisper](https://platform.openai.com/docs/guides/speech-to-text) with [OpenAI](https://platform.openai.com/docs/overview) and [Azure](https://learn.microsoft.com/azure/ai-services/openai/concepts/models) as Platform
  * All models provided by [HuggingFace](https://huggingface.co/) can be listed with `make huggingface-models`
    And more filtered with `php examples/huggingface/_model-listing.php --provider=hf-inference --task=object-detection`

See [issue #28](https://github.com/php-llm/llm-chain/issues/28) for planned support of other models and platforms.

### Chain & Messages

The core feature of LLM Chain is to interact with language models via messages. This interaction is done by sending
a **MessageBag** to a **Chain**, which takes care of LLM invocation and response handling.

Messages can be of different types, most importantly `UserMessage`, `SystemMessage`, or `AssistantMessage`, and can also
have different content types, like `Text`, `Image` or `Audio`.

#### Message Unique IDs

Each message automatically receives a unique identifier (UUID v7) upon creation.
This provides several benefits:

- **Traceability**: Track individual messages through your application
- **Time-ordered**: UUIDs are naturally sortable by creation time
- **Timestamp extraction**: Get the exact creation time from the ID
- **Database-friendly**: Sequential nature improves index performance

```php
use PhpLlm\LlmChain\Platform\Message\Message;

$message = Message::ofUser('Hello, AI!');

// Access the unique ID
$id = $message->getId(); // Returns Symfony\Component\Uid\Uuid instance

// Extract creation timestamp
$createdAt = $id->getDateTime(); // Returns \DateTimeImmutable
echo $createdAt->format('Y-m-d H:i:s.u'); // e.g., "2025-06-29 15:30:45.123456"

// Get string representation
echo $id->toRfc4122(); // e.g., "01928d1f-6f2e-7123-a456-123456789abc"
```

#### Example Chain call with messages

```php
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

// Platform & LLM instantiation

$chain = new Chain($platform, $model);
$messages = new MessageBag(
    Message::forSystem('You are a helpful chatbot answering questions about LLM Chain.'),
    Message::ofUser('Hello, how are you?'),
);
$response = $chain->call($messages);

echo $response->getContent(); // "I'm fine, thank you. How can I help you today?"
```

The `MessageInterface` and `Content` interface help to customize this process if needed, e.g. additional state handling.

#### Options

The second parameter of the `call` method is an array of options, which can be used to configure the behavior of the
chain, like `stream`, `output_structure`, or `response_format`. This behavior is a combination of features provided by
the underlying model and platform, or additional features provided by processors registered to the chain.

Options designed for additional features provided by LLM Chain can be found in this documentation. For model- and
platform-specific options, please refer to the respective documentation.

```php
// Chain and MessageBag instantiation

$response = $chain->call($messages, [
    'temperature' => 0.5, // example option controlling the randomness of the response, e.g. GPT and Claude
    'n' => 3,             // example option controlling the number of responses generated, e.g. GPT
]);
```

#### Code Examples

1. [Anthropic's Claude](examples/anthropic/chat.php)
1. [OpenAI's GPT with Azure](examples/azure/chat-gpt.php)
1. [OpenAI's GPT](examples/openai/chat.php)
1. [OpenAI's o1](examples/openai/chat-o1.php)
1. [Meta's Llama with Azure](examples/azure/chat-llama.php)
1. [Meta's Llama with Ollama](examples/ollama/chat-llama.php)
1. [Meta's Llama with Replicate](examples/replicate/chat-llama.php)
1. [Google's Gemini with Google](examples/google/chat.php)
1. [Google's Gemini with OpenRouter](examples/openrouter/chat-gemini.php)
1. [Mistral's Mistral with Mistral](examples/mistral/chat-mistral.php)
1. [Albert API (French Sovereign AI)](examples/albert/chat.php)

### Tools

To integrate LLMs with your application, LLM Chain supports [tool calling](https://platform.openai.com/docs/guides/function-calling) out of the box.
Tools are services that can be called by the LLM to provide additional features or process data.

#### Server Tools

Some platforms provide built-in server-side tools for enhanced capabilities without custom implementations:

1. **[Google Gemini](docs/google-gemini-server-tools.md)** - URL Context, Google Search, Code Execution

Tool calling can be enabled by registering the processors in the chain:

```php
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;

// Platform & LLM instantiation

$yourTool = new YourTool();

$toolbox = Toolbox::create($yourTool);
$toolProcessor = new ChainProcessor($toolbox);

$chain = new Chain($platform, $model, inputProcessors: [$toolProcessor], outputProcessors: [$toolProcessor]);
```

Custom tools can basically be any class, but must configure by the `#[AsTool]` attribute.

```php
use PhpLlm\LlmChain\Toolbox\Attribute\AsTool;

#[AsTool('company_name', 'Provides the name of your company')]
final class CompanyName
{
    public function __invoke(): string
    {
        return 'ACME Corp.';
    }
}
```

#### Tool Return Value

In the end, the tool's response needs to be a string, but LLM Chain converts arrays and objects, that implement the
`JsonSerializable` interface, to JSON strings for you. So you can return arrays or objects directly from your tool.

#### Tool Methods

You can configure the method to be called by the LLM with the `#[AsTool]` attribute and have multiple tools per class:

```php
use PhpLlm\LlmChain\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'weather_current',
    description: 'get current weather for a location',
    method: 'current',
)]
#[AsTool(
    name: 'weather_forecast',
    description: 'get weather forecast for a location',
    method: 'forecast',
)]
final readonly class OpenMeteo
{
    public function current(float $latitude, float $longitude): array
    {
        // ...
    }

    public function forecast(float $latitude, float $longitude): array
    {
        // ...
    }
}
```

#### Tool Parameters

LLM Chain generates a JSON Schema representation for all tools in the `Toolbox` based on the `#[AsTool]` attribute and
method arguments and param comments in the doc block. Additionally, JSON Schema support validation rules, which are
partially support by LLMs like GPT.

To leverage this, configure the `#[With]` attribute on the method arguments of your tool:

```php
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use PhpLlm\LlmChain\Platform\Contract\JsonSchema\Attribute\With;

#[AsTool('my_tool', 'Example tool with parameters requirements.')]
final class MyTool
{
    /**
     * @param string $name   The name of an object
     * @param int    $number The number of an object
     */
    public function __invoke(
        #[With(pattern: '/([a-z0-1]){5}/')]
        string $name,
        #[With(minimum: 0, maximum: 10)]
        int $number,
    ): string {
        // ...
    }
}
```

See attribute class [With](src/Chain/JsonSchema/Attribute/With.php) for all available options.

> [!NOTE]
> Please be aware, that this is only converted in a JSON Schema for the LLM to respect, but not validated by LLM Chain.

#### Third-Party Tools

In some cases you might want to use third-party tools, which are not part of your application. Adding the `#[AsTool]`
attribute to the class is not possible in those cases, but you can explicitly register the tool in the `MemoryFactory`:

```php
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\MemoryToolFactory;
use Symfony\Component\Clock\Clock;

$metadataFactory = (new MemoryToolFactory())
    ->addTool(Clock::class, 'clock', 'Get the current date and time', 'now');
$toolbox = new Toolbox($metadataFactory, [new Clock()]);
```

> [!NOTE]
> Please be aware that not all return types are supported by the toolbox, so a decorator might still be needed.

This can be combined with the `ChainFactory` which enables you to use explicitly registered tools and `#[AsTool]` tagged
tools in the same chain - which even enables you to overwrite the pre-existing configuration of a tool:

```php
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\ChainFactory;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\MemoryToolFactory;
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\ReflectionToolFactory;

$reflectionFactory = new ReflectionToolFactory(); // Register tools with #[AsTool] attribute
$metadataFactory = (new MemoryToolFactory())      // Register or overwrite tools explicitly
    ->addTool(...);
$toolbox = new Toolbox(new ChainFactory($metadataFactory, $reflectionFactory), [...]);
```

> [!NOTE]
> The order of the factories in the `ChainFactory` matters, as the first factory has the highest priority.

#### Chain in Chain 🤯

Similar to third-party tools, you can also use a chain as a tool in another chain. This can be useful to encapsulate
complex logic or to reuse a chain in multiple places or hide sub-chains from the LLM.

```php
use PhpLlm\LlmChain\Chain\Toolbox\ToolFactory\MemoryToolFactory;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\Chain;

// Chain was initialized before

$chainTool = new Chain($chain);
$metadataFactory = (new MemoryToolFactory())
    ->addTool($chainTool, 'research_agent', 'Meaningful description for sub-chain');
$toolbox = new Toolbox($metadataFactory, [$chainTool]);
```

#### Fault Tolerance

To gracefully handle errors that occur during tool calling, e.g. wrong tool names or runtime errors, you can use the
`FaultTolerantToolbox` as a decorator for the `Toolbox`. It will catch the exceptions and return readable error messages
to the LLM.

```php
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\FaultTolerantToolbox;

// Platform, LLM & Toolbox instantiation

$toolbox = new FaultTolerantToolbox($innerToolbox);
$toolProcessor = new ChainProcessor($toolbox);

$chain = new Chain($platform, $model, inputProcessor: [$toolProcessor], outputProcessor: [$toolProcessor]);
```

#### Tool Filtering

To limit the tools provided to the LLM in a specific chain call to a subset of the configured tools, you can use the
`tools` option with a list of tool names:

```php
$this->chain->call($messages, ['tools' => ['tavily_search']]);
```

#### Tool Result Interception

To react to the result of a tool, you can implement an EventListener or EventSubscriber, that listens to the
`ToolCallsExecuted` event. This event is dispatched after the `Toolbox` executed all current tool calls and enables
you to skip the next LLM call by setting a response yourself:

```php
$eventDispatcher->addListener(ToolCallsExecuted::class, function (ToolCallsExecuted $event): void {
    foreach ($event->toolCallResults as $toolCallResult) {
        if (str_starts_with($toolCallResult->toolCall->name, 'weather_')) {
            $event->response = new StructuredResponse($toolCallResult->result);
        }
    }
});
```

#### Keeping Tool Messages

Sometimes you might wish to keep the tool messages (`AssistantMessage` containing the `toolCalls` and `ToolCallMessage` containing the response) in the context.
Enable the `keepToolMessages` flag of the toolbox' `ChainProcessor` to ensure those messages will be added to your `MessageBag`.

```php
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;

// Platform & LLM instantiation
$messages = new MessageBag(
    Message::forSystem(<<<PROMPT
        Please answer all user questions only using the similary_search tool. Do not add information and if you cannot
        find an answer, say so.
        PROMPT),
    Message::ofUser('...') // The user's question.
);

$yourTool = new YourTool();

$toolbox = Toolbox::create($yourTool);
$toolProcessor = new ChainProcessor($toolbox, keepToolMessages: true);

$chain = new Chain($platform, $llm, inputProcessor: [$toolProcessor], outputProcessor: [$toolProcessor]);
$response = $chain->call($messages);
// $messages will now include the tool messages
```

#### Code Examples (with built-in tools)

1. [Brave Tool](examples/toolbox/brave.php)
1. [Clock Tool](examples/toolbox/clock.php)
1. [Crawler Tool](examples/toolbox/brave.php)
1. [SerpAPI Tool](examples/toolbox/serpapi.php)
1. [Tavily Tool](examples/toolbox/tavily.php)
1. [Weather Tool with Event Listener](examples/toolbox/weather-event.php)
1. [Wikipedia Tool](examples/anthropic/toolcall.php)
1. [YouTube Transcriber Tool](examples/openai/toolcall.php)

### Document Embedding, Vector Stores & Similarity Search (RAG)

LLM Chain supports document embedding and similarity search using vector stores like ChromaDB, Azure AI Search, MongoDB
Atlas Search, or Pinecone.

For populating a vector store, LLM Chain provides the service `Indexer`, which requires an instance of an
`EmbeddingsModel` and one of `StoreInterface`, and works with a collection of `Document` objects as input:

```php
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Store\Bridge\Pinecone\Store;
use PhpLlm\LlmChain\Store\Indexer;
use Probots\Pinecone\Pinecone;

$indexer = new Indexer(
    PlatformFactory::create($_ENV['OPENAI_API_KEY']),
    new Embeddings(),
    new Store(Pinecone::client($_ENV['PINECONE_API_KEY'], $_ENV['PINECONE_HOST']),
);
$indexer->index($documents);
```

The collection of `Document` instances is usually created by text input of your domain entities:

```php
use PhpLlm\LlmChain\Store\Document\Metadata;
use PhpLlm\LlmChain\Store\Document\TextDocument;

foreach ($entities as $entity) {
    $documents[] = new TextDocument(
        id: $entity->getId(),                       // UUID instance
        content: $entity->toString(),               // Text representation of relevant data for embedding
        metadata: new Metadata($entity->toArray()), // Array representation of an entity to be stored additionally
    );
}
```
> [!NOTE]
> Not all data needs to be stored in the vector store, but you could also hydrate the original data entry based
> on the ID or metadata after retrieval from the store.*

In the end the chain is used in combination with a retrieval tool on top of the vector store, e.g. the built-in
`SimilaritySearch` tool provided by the library:

```php
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Tool\SimilaritySearch;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

// Initialize Platform & Models

$similaritySearch = new SimilaritySearch($model, $store);
$toolbox = Toolbox::create($similaritySearch);
$processor = new Chain($toolbox);
$chain = new Chain($platform, $model, [$processor], [$processor]);

$messages = new MessageBag(
    Message::forSystem(<<<PROMPT
        Please answer all user questions only using the similary_search tool. Do not add information and if you cannot
        find an answer, say so.
        PROMPT),
    Message::ofUser('...') // The user's question.
);
$response = $chain->call($messages);
```

#### Code Examples

1. [MariaDB Store](examples/store/mariadb-similarity-search.php)
1. [MongoDB Store](examples/store/mongodb-similarity-search.php)
1. [Pinecone Store](examples/store/pinecone-similarity-search.php)

#### Supported Stores

* [Azure AI Search](https://azure.microsoft.com/en-us/products/ai-services/ai-search)
* [ChromaDB](https://trychroma.com) (requires `codewithkyrian/chromadb-php` as additional dependency)
* [MariaDB](https://mariadb.org/projects/mariadb-vector/) (requires `ext-pdo`)
* [MongoDB Atlas Search](https://mongodb.com/products/platform/atlas-vector-search) (requires `mongodb/mongodb` as additional dependency)
* [Pinecone](https://pinecone.io) (requires `probots-io/pinecone-php` as additional dependency)

See [issue #28](https://github.com/php-llm/llm-chain/issues/28) for planned support of other models and platforms.

## Advanced Usage & Features

### Structured Output

A typical use-case of LLMs is to classify and extract data from unstructured sources, which is supported by some models
by features like **Structured Output** or providing a **Response Format**.

#### PHP Classes as Output

LLM Chain supports that use-case by abstracting the hustle of defining and providing schemas to the LLM and converting
the response back to PHP objects.

To achieve this, a specific chain processor needs to be registered:

```php
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\StructuredOutput\ChainProcessor;
use PhpLlm\LlmChain\Chain\StructuredOutput\ResponseFormatFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Tests\Chain\StructuredOutput\Data\MathReasoning;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

// Initialize Platform and LLM

$serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
$processor = new ChainProcessor(new ResponseFormatFactory(), $serializer);
$chain = new Chain($platform, $model, [$processor], [$processor]);

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
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

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

1. [Structured Output with PHP class)](examples/openai/structured-output-math.php)
1. [Structured Output with array](examples/openai/structured-output-clock.php)

### Response Streaming

Since LLMs usually generate a response word by word, most of them also support streaming the response using Server Side
Events. LLM Chain supports that by abstracting the conversion and returning a Generator as content of the response.

```php
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;

// Initialize Platform and LLM

$chain = new Chain($model);
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

1. [Streaming Claude](examples/anthropic/stream.php)
1. [Streaming GPT](examples/openai/stream.php)
1. [Streaming Mistral](examples/mistral/stream.php)

### Image Processing

Some LLMs also support images as input, which LLM Chain supports as `Content` type within the `UserMessage`:

```php
use PhpLlm\LlmChain\Platform\Message\Content\Image;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

// Initialize Platform, LLM & Chain

$messages = new MessageBag(
    Message::forSystem('You are an image analyzer bot that helps identify the content of images.'),
    Message::ofUser(
        'Describe the image as a comedian would do it.',
        Image::fromFile(dirname(__DIR__).'/tests/Fixture/image.jpg'), // Path to an image file
        Image::fromDataUrl('data:image/png;base64,...'), // Data URL of an image
        new ImageUrl('https://foo.com/bar.png'), // URL to an image
    ),
);
$response = $chain->call($messages);
```

#### Code Examples

1. [Binary Image Input with GPT](examples/openai/image-input-binary.php)
1. [Image URL Input with GPT](examples/openai/image-input-url.php)

### Audio Processing

Similar to images, some LLMs also support audio as input, which is just another `Content` type within the `UserMessage`:

```php
use PhpLlm\LlmChain\Platform\Message\Content\Audio;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

// Initialize Platform, LLM & Chain

$messages = new MessageBag(
    Message::ofUser(
        'What is this recording about?',
        Audio::fromFile(dirname(__DIR__).'/tests/Fixture/audio.mp3'), // Path to an audio file
    ),
);
$response = $chain->call($messages);
```

#### Code Examples

1. [Audio Input with GPT](examples/openai/audio-input.php)

### Embeddings

Creating embeddings of word, sentences, or paragraphs is a typical use case around the interaction with LLMs, and
therefore LLM Chain implements a `EmbeddingsModel` interface with various models, see above.

The standalone usage results in an `Vector` instance:

```php
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;

// Initialize Platform

$embeddings = new Embeddings($platform, Embeddings::TEXT_3_SMALL);

$vectors = $platform->request($embeddings, $textInput)->asVectors();

dump($vectors[0]->getData()); // Array of float values
```

#### Code Examples

1. [OpenAI's Emebddings](examples/openai/embeddings.php)
1. [Voyage's Embeddings](examples/voyage/embeddings.php)
1. [Mistral's Embed](examples/mistral/embeddings.php)

### Parallel Platform Calls

Platform supports multiple model calls in parallel, which can be useful to speed up the processing:

```php
// Initialize Platform & Model

foreach ($inputs as $input) {
    $responses[] = $platform->request($model, $input);
}

foreach ($responses as $response) {
    echo $response->asText().PHP_EOL;
}
```

> [!NOTE]
> This requires cURL and the `ext-curl` extension to be installed.

#### Code Examples

1. [Parallel GPT Calls](examples/parallel-chat-gpt.php)
1. [Parallel Embeddings Calls](examples/parallel-embeddings.php)

> [!NOTE]
> Please be aware that some embedding models also support batch processing out of the box.

### Input & Output Processing

The behavior of the Chain is extendable with services that implement `InputProcessor` and/or `OutputProcessor`
interface. They are provided while instantiating the Chain instance:

```php
use PhpLlm\LlmChain\Chain\Chain;

// Initialize Platform, LLM and processors

$chain = new Chain($platform, $model, $inputProcessors, $outputProcessors);
```

#### InputProcessor

`InputProcessor` instances are called in the chain before handing over the `MessageBag` and the `$options` array to the LLM and are
able to mutate both on top of the `Input` instance provided.

```php
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessorInterface;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;

final class MyProcessor implements InputProcessorInterface
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
use PhpLlm\LlmChain\Chain\OutputProcessorInterface;

final class MyProcessor implements OutputProcessorInterface
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
use PhpLlm\LlmChain\Chain\ChainAwareInterface;
use PhpLlm\LlmChain\Chain\ChainAwareTrait;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessorInterface;

final class MyProcessor implements OutputProcessorInterface, ChainAwareInterface
{
    use ChainAwareTrait;

    public function processOutput(Output $out): void
    {
        // additional chain interaction
        $response = $this->chain->call(...);
    }
}
```


## Memory

LLM Chain supports adding contextual memory to your conversations, which allows the model to recall past interactions or relevant information from different sources. Memory providers inject information into the system prompt, providing the model with context without changing your application logic.

### Using Memory

Memory integration is handled through the `MemoryInputProcessor` and one or more `MemoryProviderInterface` implementations. Here's how to set it up:

```php
<?php
use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Memory\MemoryInputProcessor;
use PhpLlm\LlmChain\Chain\Memory\StaticMemoryProvider;

// Platform & LLM instantiation

$personalFacts = new StaticMemoryProvider(
    'My name is Wilhelm Tell',
    'I wish to be a swiss national hero',
    'I am struggling with hitting apples but want to be professional with the bow and arrow',
);
$memoryProcessor = new MemoryInputProcessor($personalFacts);

$chain = new Chain($platform, $model, [$memoryProcessor]);
$messages = new MessageBag(Message::ofUser('What do we do today?'));
$response = $chain->call($messages);
```

### Memory Providers

The library includes some implementations that are usable out of the box.

#### Static Memory

The static memory can be utilized to provide static information form, for example, user settings, basic knowledge of your application
or any other thing that should be remembered als always there without the need of having it statically added to the system prompt by
yourself.

```php
use PhpLlm\LlmChain\Chain\Memory\StaticMemoryProvider;

$staticMemory = new StaticMemoryProvider(
    'The user is allergic to nuts',
    'The user prefers brief explanations',
);
```

#### Embedding Provider

Based on an embedding storage the given user message is utilized to inject knowledge from the storage. This could be general knowledge that was stored there and could fit the users input without the need for tools or past conversation pieces that should be recalled for
the current message bag.

```php
use PhpLlm\LlmChain\Chain\Memory\EmbeddingProvider;

$embeddingsMemory = new EmbeddingProvider(
    $platform,
    $embeddings, // Your embeddings model to use for vectorizing the users message
    $store       // Your vector store to query for fitting context
);

```

### Dynamically Memory Usage

The memory configuration is globally given for the chain. Sometimes there is the need to explicit disable the memory when it is not needed for some calls or calls are not in the wanted context for a call. So there is the option `use_memory` that is enabled by default but can be disabled on premise.

```php
$response = $chain->call($messages, [
    'use_memory' => false,
]);
```


## HuggingFace

LLM Chain comes out of the box with an integration for [HuggingFace](https://huggingface.co/)  which is a platform for
hosting and sharing all kinds of models, including LLMs, embeddings, image generation, and classification models.

You can just instantiate the Platform with the corresponding HuggingFace bridge and use it with the `task` option:

```php
use PhpLlm\LlmChain\Bridge\HuggingFace\Model;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\PlatformFactory;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Task;
use PhpLlm\LlmChain\Platform\Message\Content\Image;

$platform = PlatformFactory::create($apiKey);
$model = new Model('facebook/detr-resnet-50');

$image = Image::fromFile(dirname(__DIR__, 2).'/tests/Fixture/image.jpg');
$response = $platform->request($model, $image, [
    'task' => Task::OBJECT_DETECTION, // defining a task is mandatory for internal request & response handling
]);

dump($response->asObject());
```

#### Code Examples

1. [Audio Classification](examples/huggingface/audio-classification.php)
1. [Automatic Speech Recognition](examples/huggingface/automatic-speech-recognition.php)
1. [Chat Completion](examples/huggingface/chat-completion.php)
1. [Feature Extraction (Embeddings)](examples/huggingface/feature-extraction.php)
1. [Fill Mask](examples/huggingface/fill-mask.php)
1. [Image Classification](examples/huggingface/image-classification.php)
1. [Image Segmentation.php](examples/huggingface/image-segmentation.php)
1. [Image-to-Text](examples/huggingface/image-to-text.php)
1. [Object Detection](examples/huggingface/object-detection.php)
1. [Question Answering](examples/huggingface/question-answering.php)
1. [Sentence Similarity](examples/huggingface/sentence-similarity.php)
1. [Summarization](examples/huggingface/summarization.php)
1. [Table Question Answering](examples/huggingface/table-question-answering.php)
1. [Text Classification](examples/huggingface/text-classification.php)
1. [Text Generation](examples/huggingface/text-generation.php)
1. [Text-to-Image](examples/huggingface/text-to-image.php)
1. [Token Classification](examples/huggingface/token-classification.php)
1. [Translation](examples/huggingface/translation.php)
1. [Zero-shot Classification](examples/huggingface/zero-shot-classification.php)

## TransformerPHP

With installing the library `codewithkyrian/transformers` it is possible to run [ONNX](https://onnx.ai/) models locally
without the need of an extra tool like Ollama or a cloud service. This requires [FFI](https://www.php.net/manual/en/book.ffi.php)
and comes with an extra setup, see [TransformersPHP's Getting Starter](https://transformers.codewithkyrian.com/getting-started).

The usage with LLM Chain is similar to the HuggingFace integration, and also requires the `task` option to be set:

```php
use Codewithkyrian\Transformers\Pipelines\Task;
use PhpLlm\LlmChain\Bridge\TransformersPHP\Model;
use PhpLlm\LlmChain\Platform\Bridge\TransformersPHP\PlatformFactory;

$platform = PlatformFactory::create();
$model = new Model('Xenova/LaMini-Flan-T5-783M');

$response = $platform->request($model, 'How many continents are there in the world?', [
    'task' => Task::Text2TextGeneration,
]);

echo $response->asText().PHP_EOL;
```

#### Code Examples

1. [Text Generation with TransformersPHP](examples/transformers/text-generation.php)

## Contributions

Contributions are always welcome, so feel free to join the development of this library. To get started, please read the
[contribution guidelines](CONTRIBUTING.md).

### Current Contributors

[![LLM Chain Contributors](https://contrib.rocks/image?repo=php-llm/llm-chain 'LLM Chain Contributors')](https://github.com/php-llm/llm-chain/graphs/contributors)

Made with [contrib.rocks](https://contrib.rocks).

### Fixture Licenses

For testing multi-modal features, the repository contains binary media content, with the following owners and licenses:

* `tests/Fixture/image.jpg`: Chris F., Creative Commons, see [pexels.com](https://www.pexels.com/photo/blauer-und-gruner-elefant-mit-licht-1680755/)
* `tests/Fixture/audio.mp3`: davidbain, Creative Commons, see [freesound.org](https://freesound.org/people/davidbain/sounds/136777/)
* `tests/Fixture/document.pdf`: Chem8240ja, Public Domain, see [Wikipedia](https://en.m.wikipedia.org/wiki/File:Re_example.pdf)
