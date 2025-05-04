<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Ollama;

use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class LlamaModelHandler implements ModelClient, ResponseConverter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $hostUrl,
    ) {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof Llama;
    }

    public function request(Model $model, array|string $payload, array $options = ['stream' => false]): ResponseInterface
    {
        return $this->httpClient->request('POST', sprintf('%s/api/chat', $this->hostUrl), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => array_merge($options, $payload),
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        if (!isset($data['message'])) {
            throw new RuntimeException('Response does not contain message');
        }

        if (!isset($data['message']['content'])) {
            throw new RuntimeException('Message does not contain content');
        }

        return new TextResponse($data['message']['content']);
    }
}
