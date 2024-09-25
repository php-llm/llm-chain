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
| **Anthropic**  | - Claude | - Anthropic          |

Planned Models & Platforms (not implemented yet):

| Vendor         | Model                  | Platform                         |
|----------------|------------------------|----------------------------------|
| **Anthropic**  | - Voyage | - GPC<br/>- AWS                  |
| **Google**     | - Gemini<br/>- Gemma | - GPC                            |
| **Meta**       | - Llama | - Meta AI<br/>- GPC<br/>- Ollama |
| **Mistral AI** | - Mistral<br/>- Codestral | - Mistral<br/>- GPT<br/>- Ollama |

Supported Stores
----------------

* [x] [Chroma](https://trychroma.com)
* [x] [Azure AI Search](https://azure.microsoft.com/en-us/products/ai-services/ai-search)
* [ ] [Pinecone](https://pinecone.io)

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
Depending on the example you need to export different environment variables for API keys or deployment configurations:

### Chat Examples

1. Chat Example: OpenAI's GPT
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/chat-gpt-openai.php
   ```
   
1. Chat Example: OpenAI's GPT With Azure
   ```bash
   export AZURE_OPENAI_BASEURL=... // e.g. your-resource.openai.azure.com
   export AZURE_OPENAI_DEPLOYMENT=...
   export AZURE_OPENAI_VERSION=... // e.g. 2023-03-15-preview
   export AZURE_OPENAI_KEY=...
   php examples/chat-gpt-azure.php
   ```
 
1. Chat Example: Anthropic's Claude
   ```bash
   export ANTHROPIC_API_KEY=sk-...
   php examples/chat-claude-anthropic.php
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

### Structured Output

1. Structured Output Example: OpenAI's GPT
   ```bash
   export OPENAI_API_KEY=sk-...
   php examples/structured-output-math.php
   ```

Contributions
-------------

Contributions are always welcome.
