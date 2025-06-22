# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the **PHP LLM Chain** library - a PHP abstraction layer for working with Large Language Models (LLMs) from various providers like OpenAI, Anthropic, Google, Azure, and others. The library provides a unified interface for AI model interactions, tool calling, vector storage, and multi-modal content processing.

**Key Features:**
- Multi-platform LLM support (OpenAI GPT, Anthropic Claude, Google Gemini, Meta Llama, etc.)
- Tool calling with automatic PHP method binding
- Vector stores and RAG (Retrieval Augmented Generation)
- Multi-modal input (text, images, audio, documents)
- Streaming responses and structured output
- Extensible architecture with processor patterns

## Development Commands

### Dependencies
```bash
composer install
composer update --prefer-stable --ignore-platform-req=ext-mongodb
```

### Code Quality
```bash
make cs                    # Fix code style with PHP-CS-Fixer
make rector                # Apply Rector refactoring rules
make phpstan               # Run PHPStan static analysis
```

### Testing
```bash
make tests                 # Run PHPUnit tests
make coverage              # Generate test coverage report
vendor/bin/phpunit         # Run tests directly
```

### Examples
```bash
make run-examples          # Run all examples
./example                  # Run all examples (executable script)
./example anthropic        # Run only Anthropic examples
./example huggingface      # Run only HuggingFace examples
make huggingface-models    # List available HuggingFace models
```

### CI Pipeline
```bash
make ci                    # Run full CI pipeline (stable deps)
make ci-stable             # Run CI with stable dependencies
make ci-lowest             # Run CI with lowest dependencies
```

## Core Architecture

### Chain Pattern
The library follows a **Chain of Responsibility** pattern:
- `Chain` - Main orchestrator that processes input/output
- `PlatformInterface` - Abstracts different LLM providers
- `MessageBag` - Collection of conversation messages
- `InputProcessor`/`OutputProcessor` - Extensible processing pipeline

### Platform Bridges
Each LLM provider has a bridge implementation:
- `src/Platform/Bridge/OpenAI/` - OpenAI GPT, DALL-E, Whisper
- `src/Platform/Bridge/Anthropic/` - Claude models
- `src/Platform/Bridge/Google/` - Gemini models
- `src/Platform/Bridge/Bedrock/` - AWS Bedrock (Claude, Llama, Nova)
- `src/Platform/Bridge/HuggingFace/` - HuggingFace models

### Message System
Multi-modal message types:
- `UserMessage` - User input (text, images, audio, documents)
- `AssistantMessage` - AI responses with optional tool calls
- `SystemMessage` - System prompts
- `ToolCallMessage` - Tool execution results

### Tool System
- `#[AsTool]` attribute marks PHP methods as LLM-callable tools
- `Toolbox` manages tool execution and metadata
- `ToolFactory` generates tool schemas from PHP reflection
- Built-in tools: Wikipedia, Weather, Web Search, etc.

### Vector Stores
- `Embedder` converts text to vectors using embedding models
- `VectorStoreInterface` for similarity search (RAG)
- Supported stores: ChromaDB, MongoDB Atlas, Pinecone, Azure AI Search

## Adding New Features

### New Platform Support
1. Create bridge in `src/Platform/Bridge/YourPlatform/`
2. Implement `PlatformFactory`, `ModelClient`, `ResponseConverter`
3. Add platform-specific normalizers for message/tool conversion
4. Create model classes with capability definitions

### New Tool Implementation
```php
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;

#[AsTool('tool_name', 'Tool description')]
class YourTool
{
    public function __invoke(string $param): string
    {
        return 'result';
    }
}
```

### Custom Processors
Implement `InputProcessorInterface` or `OutputProcessorInterface` to modify chain behavior.

## Testing Strategy

- **Unit Tests**: Test individual components in isolation
- **Integration Tests**: Test platform bridges with real API calls
- **Fixture Tests**: Use test fixtures for reproducible results
- **Coverage**: Aim for high test coverage with metadata requirements

## Key Dependencies

- **Symfony Components**: HTTP client, serializer, console, type system
- **PHPStan**: Static analysis with level 6
- **PHP-CS-Fixer**: Code style enforcement
- **Rector**: Automated refactoring
- **PHPUnit**: Testing framework

## Environment Setup

Examples require API keys as environment variables:
- `OPENAI_API_KEY` - OpenAI platform
- `ANTHROPIC_API_KEY` - Anthropic platform  
- `GOOGLE_API_KEY` - Google AI platform
- Platform-specific keys for Azure, AWS, etc.

Create `.env.local` based on `.env` file for local development.

## Common Patterns

1. **Factory Pattern**: Use `PlatformFactory::create()` for platform setup
2. **Builder Pattern**: Chain configuration with input/output processors
3. **Strategy Pattern**: Different model clients per platform
4. **Decorator Pattern**: `FaultTolerantToolbox` for error handling
5. **Async Pattern**: `AsyncResponse` for lazy HTTP response processing

## File Structure

- `src/Chain/` - Core chain logic and processors
- `src/Platform/` - Platform abstraction and bridge implementations
- `src/Store/` - Vector store implementations
- `examples/` - Usage examples for all platforms
- `tests/` - Comprehensive test suite