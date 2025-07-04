<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Albert;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class GPTModelClient implements ModelClientInterface
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
        private string $baseUrl,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
        Assert::stringNotEmpty($baseUrl, 'The base URL must not be empty.');
    }

    public function supports(Model $model): bool
    {
        return $model instanceof GPT;
    }

    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', $this->baseUrl.'chat/completions', [
            'auth_bearer' => $this->apiKey,
            'json' => array_merge($options, $payload),
        ]);
    }
}
