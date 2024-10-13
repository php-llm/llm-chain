<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Azure\OpenAI;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\ModelClient;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class EmbeddingsModelClient implements ModelClient
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        private string $baseUrl,
        private string $deployment,
        private string $apiVersion,
        #[\SensitiveParameter] private string $apiKey,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        Assert::notStartsWith($baseUrl, 'http://', 'The base URL must not contain the protocol.');
        Assert::notStartsWith($baseUrl, 'https://', 'The base URL must not contain the protocol.');
        Assert::stringNotEmpty($deployment, 'The deployment must not be empty.');
        Assert::stringNotEmpty($apiVersion, 'The API version must not be empty.');
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
    }

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Embeddings;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        $url = sprintf('https://%s/openai/deployments/%s/embeddings', $this->baseUrl, $this->deployment);

        return $this->httpClient->request('POST', $url, [
            'headers' => [
                'api-key' => $this->apiKey,
            ],
            'query' => ['api-version' => $this->apiVersion],
            'json' => array_merge($options, [
                'model' => $model->getVersion(),
                'input' => $input,
            ]),
        ]);
    }
}
