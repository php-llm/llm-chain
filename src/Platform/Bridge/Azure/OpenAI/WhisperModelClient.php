<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Azure\OpenAI;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class WhisperModelClient implements ModelClientInterface
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

    public function supports(Model $model): bool
    {
        return $model instanceof Whisper;
    }

    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        $url = \sprintf('https://%s/openai/deployments/%s/audio/translations', $this->baseUrl, $this->deployment);

        return $this->httpClient->request('POST', $url, [
            'headers' => [
                'api-key' => $this->apiKey,
                'Content-Type' => 'multipart/form-data',
            ],
            'query' => ['api-version' => $this->apiVersion],
            'body' => array_merge($options, $payload),
        ]);
    }
}
