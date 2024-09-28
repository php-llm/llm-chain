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

Supported Models & Platforms
----------------------------

Currently supported models and platforms:

| Vendor         | Model                  | Platform             |
|----------------|------------------------|----------------------|
| **OpenAI**     | - GPT<br/>- Embeddings | - OpenAI<br/>- Azure |
| **Anthropic**  | - Claude               | - Anthropic          |
| **Voyage**     | - Voyage               | - Voyage             |

Planned Models & Platforms (not implemented yet):

| Vendor         | Model                     | Platform                         |
|----------------|---------------------------|----------------------------------|
| **Anthropic**  | - Claude                  | - GPC<br/>- AWS                  |
| **Voyage**     | - Voyage                  | - AWS                            |
| **Google**     | - Gemini<br/>- Gemma      | - GPC                            |
| **Meta**       | - Llama                   | - Meta AI<br/>- GPC<br/>- Ollama |
| **Mistral AI** | - Mistral<br/>- Codestral | - Mistral<br/>- GPT<br/>- Ollama |

Supported Stores
----------------

* [x] [ChromaDB](https://trychroma.com)
* [x] [Azure AI Search](https://azure.microsoft.com/en-us/products/ai-services/ai-search)
* [x] [MongoDB Atlas Search](https://mongodb.com/products/platform/atlas-vector-search)
* [x] [Pinecone](https://pinecone.io)
* [ ] [Milvus](https://milvus.io)
* [ ] [Weaviate](https://weaviate.io)
* [ ] 

Provided Tools
--------------

* [x] Clock
* [x] SerpApi
* [x] Similarity Search (Basic)
* [x] Wikipedia
* [x] Weather
* [x] YouTube Transcriber

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
