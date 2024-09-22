LLM Chain
=========

Simple PHP toolkit for building LLM chains.

This is not stable nor production ready, it's just a playground for me to experiment with LLMs.
Abstractions, concepts and interfaces are not good at all and will definitely change.

Requirements
------------
* PHP 8.2 or higher

Installation
------------

The recommended way to install LlmChain is through [Composer](http://getcomposer.org/):

```bash
composer require php-llm/llm-chain
```

Supported Models & Runtimes
---------------------------

Currently supported models and runtimes:

| Vendor         | Model                  | Runtime                          |
|----------------|------------------------|----------------------------------|
| **OpenAI**     | - GPT<br/>- Embeddings | - OpenAI<br/>- Azure             |
| **Anthropic**  | - Claude | - Anthropic  |

Planned Models & Runtimes (not implemented yet):

| Vendor         | Model                  | Runtime                          |
|----------------|------------------------|----------------------------------|
| **Anthropic**  | - Voyage | - GPC<br/>- AWS                  |
| **Google**     | - Gemini<br/>- Gemma | - GPC                            |
| **Meta**       | - Llama | - Meta AI<br/>- GPC<br/>- Ollama |
| **Mistral AI** | - Mistral<br/>- Codestral | - Mistral<br/>- GPT<br/>- Ollama |

Supported Stores
----------------

Currently supported stores:

* [x] Chroma DB
* [x] Azure AI Search
* [ ] Pinecone

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
Depending on the example you need to export needed environment variables for API keys or deployment configurations:

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

### ToolChain Examples

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
