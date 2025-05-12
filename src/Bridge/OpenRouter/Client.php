<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenRouter;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

final readonly class Client implements ModelClient, ResponseConverter
{
    private EventSourceHttpClient $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
    ) {
        $this->httpClient = $httpClient instanceof EventSourceHttpClient ? $httpClient : new EventSourceHttpClient($httpClient);
        Assert::stringNotEmpty($apiKey, 'The API key must not be empty.');
        Assert::startsWith($apiKey, 'sk-', 'The API key must start with "sk-".');
    }

    public function supports(Model $model, array|string|object $input): bool
    {
        return $input instanceof MessageBagInterface;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
            'auth_bearer' => $this->apiKey,
            'json' => array_merge($options, [
                'model' => $model->getName(),
                'messages' => $input,
            ]),
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        if (!isset($data['choices'][0]['message'])) {
            throw new RuntimeException('Response does not contain message');
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new RuntimeException('Message does not contain content');
        }

        return new TextResponse($data['choices'][0]['message']['content']);
    }
}
