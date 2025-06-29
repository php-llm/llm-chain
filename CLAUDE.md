# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Development Setup
```bash
# Install dependencies (with stable versions)
make deps-stable

# Install dependencies (with lowest versions for compatibility testing)
make deps-low
```

### Testing
```bash
# Run all tests
make tests

# Run tests with code coverage
make coverage

# Run a single test file
vendor/bin/phpunit tests/Path/To/TestFile.php

# Run a single test method
vendor/bin/phpunit tests/Path/To/TestFile.php --filter testMethodName
```

### Code Quality
```bash
# Run complete CI pipeline (rector + cs + phpstan + tests)
make ci

# Run PHP CS Fixer (code style)
make cs

# Run PHPStan (static analysis, level 6)
make phpstan

# Run Rector (automated refactoring)
make rector
```

### Running Examples
```bash
# Run all examples
./example

# Run examples from specific platform
./example anthropic
./example openai
./example huggingface

# Filter examples by pattern
./example --filter audio
./example anthropic --filter chat
```

## Architecture Overview

This is a PHP library for building LLM-based applications with a unified abstraction layer for multiple AI providers.

### Core Architecture

1. **Platform Abstraction**: The `src/Platform/` directory contains the `PlatformInterface` and bridges for each LLM provider (OpenAI, Anthropic, Google, etc.). Each bridge translates the unified API to provider-specific implementations.

2. **Chain Pattern**: The `src/Chain/` directory implements a chain of responsibility pattern with input/output processors that can transform requests and responses. This allows for features like structured output, tool calling, and custom processing.

3. **Message System**: Uses a `MessageBag` pattern for managing conversations with support for different message types (user, assistant, system, tool calls/results).

4. **Tool Calling**: The `Toolbox` system allows registering PHP methods as tools that LLMs can call. Uses attributes (`#[AsTool]`) for automatic schema generation.

5. **Vector Stores**: The `src/Store/` directory provides abstractions for vector databases used in RAG (Retrieval Augmented Generation) applications.

### Key Design Patterns

- **Bridge Pattern**: Platform bridges abstract provider-specific implementations
- **Chain of Responsibility**: Processors can be chained to transform input/output
- **Factory Pattern**: Platform factories create configured instances
- **Immutable Objects**: Extensive use of readonly properties for value objects
- **Interface-based Design**: All major components have interfaces for extensibility

### Adding New Features

1. **New Platform Support**: Create a new bridge in `src/Platform/` implementing `PlatformInterface`
2. **New Processor**: Implement `InputProcessor` or `OutputProcessor` in `src/Chain/`
3. **New Vector Store**: Implement `VectorStoreInterface` in `src/Store/`
4. **New Tool**: Add methods with `#[AsTool]` attribute to make them callable by LLMs

### Testing Strategy

- Unit tests for all components with PHPUnit
- Strict mode enabled (fail on warnings, risky tests)
- Examples serve as integration tests
- Use test doubles and fixtures in `tests/Fixtures/`

### Code Standards

- PHP 8.2+ features (readonly properties, attributes, enums)
- Strict types declaration required
- PHPStan level 6 compliance
- PSR-12 coding style (enforced by PHP CS Fixer)
- Comprehensive PHPDoc comments for public APIs