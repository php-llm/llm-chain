LlmChain
========

Simple PHP toolkit for building LLM chains.

This is not stable nor production ready, it's just a playground for me to experiment with LLMs.
Abstractions, concepts and interfaces are not good at all and will definitely change.

Requirements
------------
* PHP 8.1 or higher

Installation
------------

The recommended way to install LlmChain is through [Composer](http://getcomposer.org/):

```bash
composer require php-llm/llm-chain
```

Usage Examples
--------------

See [examples](examples) - to run the examples, you need to export
the needed environment variables with your API key:

```bash
export OPENAI_API_KEY=sk-...
export SERP_API_KEY=...
```

For the Azure example you need to export:

```bash
export AZURE_OPENAI_RESOURCE=...
export AZURE_OPENAI_DEPLOYMENT=...
export AZURE_OPENAI_VERSION=...
export AZURE_OPENAI_KEY=...
```

For Anthropic's Claude you need to export:

```bash
export ANTHROPIC_API_KEY=sk-...
```
