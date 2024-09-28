LLM Chain
=========

PHP library for building LLM-based features and applications.

This library is not a stable yet, but still rather experimental. Feel free to try it out, give feedback, ask questions, contribute or share your use cases.
Abstractions, concepts and interfaces are not final and potentially subject of change.

Requirements
------------
* PHP 8.2 or higher

Installation
------------

The recommended way to install LLM Chain is through [Composer](http://getcomposer.org/):

```bash
composer require php-llm/llm-chain
```

When using Symfony Framework, check out the integration bundle [php-llm/llm-chain-bundle](https://github.com/php-llm/llm-chain-bundle).

Supported Models, Platforms & Stores
------------------------------------

Currently supported models and platforms:

| Language Model                                                                        | Embeddings Model                                                                              | Platform                                            | Store                                                                               |
|---------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------|-----------------------------------------------------|-------------------------------------------------------------------------------------|
| [GPT](https://platform.openai.com/docs/models/overview)                               | [OpenAI Text Embeddings](https://platform.openai.com/docs/guides/embeddings/embedding-models) | [OpenAI](https://platform.openai.com/docs/overview) | [ChromaDB](https://trychroma.com)                                                   | 
| [Anthropic Claude](https://www.anthropic.com/claude)<br />_(only partially done yet)_ | [Voyage Embeddings](https://docs.voyageai.com/docs/embeddings)                                | [Anthropic](https://www.anthropic.com/)             | [Azure AI Search](https://azure.microsoft.com/en-us/products/ai-services/ai-search) |
|                                                                                       |                                                                                               | [Voyage](https://www.voyageai.com/)                 | [MongoDB Atlas Search](https://mongodb.com/products/platform/atlas-vector-search)   |
|                                                                                       |                                                                                               | [Google](https://cloud.google.com/ai)               | [Pinecone](https://pinecone.io)                                                     |

[Support will be extended to other models, platforms and stores in the future.](https://github.com/php-llm/llm-chain/issues/28) 

Provided Tools
--------------

LLM Chain provides a set of tools to be registered with language models out of the box:

* **Clock** Tool to provide current time and date.
* **SerpApi** Tool to search the web using SerpApi.
* **Similarity Search (Basic)** Tool to search for similar items a vector store.
* **Wikipedia** Tool to search Wikipedia and provide articles.
* **Weather** Tool to provide weather information via OpenMeteo.
* **YouTube Transcriber** Tool to fetch transcriptions from YouTube videos.

Usage Examples
--------------

See [examples](examples) to run example implementations using this library.
Depending on the example you need to export different environment variables
for API keys or deployment configurations or create a `.env.local` based on `.env` file.

To run all examples, just use `make run-all-examples`.

### Chat Examples

1. OpenAI's GPT
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/chat-gpt-openai.php
   ```
   
1. OpenAI's GPT with Azure
   ```bash
   export AZURE_OPENAI_BASEURL=... // e.g. your-resource.openai.azure.com
   export AZURE_OPENAI_DEPLOYMENT=...
   export AZURE_OPENAI_VERSION=... // e.g. 2023-03-15-preview
   export AZURE_OPENAI_KEY=...
   php examples/chat-gpt-azure.php
   ```
 
1. Anthropic's Claude
   ```bash
   export ANTHROPIC_API_KEY=sk-...
   php examples/chat-claude-anthropic.php
   ```

### Embeddings Examples

1. OpenAI's Emebddings
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/embeddings-openai.php
   ```

1. Voyage's Embeddings
   ```bash
   export VOYAGE_API_KEY=sk-...
   php examples/embeddings-voyage.php
    ```

### Tool Examples

1. Simple Clock Tool
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/toolbox-clock.php
   ```

1. Wikipedia Tool
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/toolbox-wikipedia.php
   ```

1. SerpAPI Tool
   ```bash
   export OPENAI_API_KEY=sk-...
   export SERPAPI_API_KEY=...
   php examples/toolbox-serpapi.php
   ```

1. Weather Tool
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/toolbox-weather.php
   ```

1. YouTube Transcriber Tool
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/toolbox-youtube.php
   ```

### Structured Output Example

1. Structured Output Example: OpenAI's GPT
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/structured-output-math.php
   ```
   
### Reasoning Example

1. Reasoning Example: OpenAI's o1
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/reasoning-openai.php
   ```

Contributions
-------------

Contributions are always welcome.
