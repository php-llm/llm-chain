<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAICompatible;

use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\ModelClient;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class Client implements ModelClient
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
        private string $baseUrl,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);

        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');

        Assert::stringNotEmpty($baseUrl, 'Base URL must not be empty.');
        Assert::startsWith($baseUrl, 'http', 'Base URL must have a valid protocol.');
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $input instanceof MessageBagInterface && $model instanceof GenericModel;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', sprintf('%s/chat/completions', $this->baseUrl), [
            'auth_bearer' => $this->apiKey,
            'json' => array_merge($options, [
                'model' => $model->getName(),
                'messages' => $input,
            ]),
        ]);
    }
}
