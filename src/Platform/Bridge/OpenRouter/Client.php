<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenRouter;

use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Webmozart\Assert\Assert;

/**
 * @author rglozman
 */
final readonly class Client implements ModelClientInterface, ResponseConverterInterface
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

    public function supports(Model $model): bool
    {
        return true;
    }

    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', 'https://openrouter.ai/api/v1/chat/completions', [
            'auth_bearer' => $this->apiKey,
            'json' => array_merge($options, $payload),
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        dump($response->getContent(false));

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
