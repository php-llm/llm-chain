<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Mistral\Embeddings;

use PhpLlm\LlmChain\Bridge\Mistral\Embeddings;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\ModelClient as PlatformResponseFactory;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class ModelClient implements PlatformResponseFactory
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Embeddings;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', 'https://api.mistral.ai/v1/embeddings', [
            'auth_bearer' => $this->apiKey,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => array_merge($options, [
                'model' => $model->getName(),
                'input' => $input,
            ]),
        ]);
    }
}
