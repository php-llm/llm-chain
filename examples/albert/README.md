# Albert API Examples

[Albert API](https://github.com/etalab-ia/albert-api) is an open-source generative AI API gateway developed by the French government. It provides a sovereign AI solution with OpenAI-compatible APIs, making it easy to integrate with LLM Chain.

## Prerequisites

1. Deploy Albert API following the [official deployment guide](https://github.com/etalab-ia/albert-api)
2. Obtain an API key from your Albert instance
3. Set the required environment variables:

```bash
export ALBERT_API_KEY="your-api-key"
export ALBERT_API_URL="https://your-albert-instance.com"
```

## Example

### RAG (Retrieval-Augmented Generation)
```bash
php examples/albert/rag.php
```
Shows how to use Albert's built-in RAG capabilities with document context.


## Configuration

Albert API is OpenAI-compatible, so it works seamlessly with LLM Chain's OpenAI bridge:

```php
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;

$platform = PlatformFactory::create(
    apiKey: $albertApiKey,
    baseUrl: rtrim($albertApiUrl, '/').'/v1/',
);
```

## Features

- **Sovereign AI**: Host your models on your own infrastructure
- **OpenAI Compatible**: Works with existing OpenAI integrations
- **Built-in RAG**: Native support for retrieval-augmented generation
- **Multiple Backends**: Supports OpenAI, vLLM, and HuggingFace models
- **Enterprise Ready**: Authentication, load balancing, and monitoring

## Notes

- Albert will route requests to the appropriate backend based on your deployment configuration
- Albert supports various model backends (OpenAI, vLLM, HuggingFace TEI)
- Check your Albert instance documentation for available models and features