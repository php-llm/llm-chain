<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\HuggingFace;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ApiClient
{
    public function __construct(
        private ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    /**
     * @return Model[]
     */
    public function models(?string $provider, ?string $task): array
    {
        $response = $this->httpClient->request('GET', 'https://huggingface.co/api/models', [
            'query' => [
                'inference_provider' => $provider,
                'pipeline_tag' => $task,
            ],
        ]);

        return array_map(fn (array $model) => new Model($model['id']), $response->toArray());
    }
}
