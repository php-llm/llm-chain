<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\ModelClient as PlatformResponseFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class ModelClient implements PlatformResponseFactory
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
    ) {
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
        Assert::startsWith($apiKey, 'sk-', 'The API key must start with "sk-".');
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof Embeddings;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', 'https://api.openai.com/v1/embeddings', [
            'auth_bearer' => $this->apiKey,
            'json' => array_merge($model->getOptions(), $options, [
                'model' => $model->getVersion(),
                'input' => $input,
            ]),
        ]);
    }
}
