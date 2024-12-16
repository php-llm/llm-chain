<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Ollama;

use http\Exception\RuntimeException;
use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Model\Message\MessageBag;
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

    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Llama && $input instanceof MessageBag;
    }

    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', sprintf('%s/api/chat', $this->hostUrl), [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'model' => $model->getVersion(),
                'messages' => $input,
                'stream' => false,
            ],
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        $data = $response->toArray(false);

        if (!isset($data['message'])) {
            throw new RuntimeException('Response does not contain message');
        }

        if (!isset($data['message']['content'])) {
            throw new RuntimeException('Message does not contain content');
        }

        return new TextResponse($data['message']['content']);
    }
}
