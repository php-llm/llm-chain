<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Azure\Meta;

use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class LlamaHandler implements ModelClient, ResponseConverter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $baseUrl,
        #[\SensitiveParameter] private string $apiKey,
    ) {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof Llama;
    }

    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        $url = sprintf('https://%s/chat/completions', $this->baseUrl);

        return $this->httpClient->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->apiKey,
            ],
            'json' => array_merge($options, $payload),
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        if (!isset($data['choices'][0]['message']['content'])) {
            throw new RuntimeException('Response does not contain output');
        }

        return new TextResponse($data['choices'][0]['message']['content']);
    }
}
