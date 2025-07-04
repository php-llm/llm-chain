<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Albert;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class EmbeddingsModelClient implements ModelClientInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
        private string $baseUrl,
    ) {
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
        Assert::stringNotEmpty($baseUrl, 'The base URL must not be empty.');
    }

    public function supports(Model $model): bool
    {
        return $model instanceof Embeddings;
    }

    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', $this->baseUrl.'embeddings', [
            'auth_bearer' => $this->apiKey,
            'json' => array_merge($options, $payload),
        ]);
    }
}
