<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Voyage;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class ModelHandler implements ModelClient, ResponseConverter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter] private string $apiKey,
    ) {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof Voyage;
    }

    public function request(Model $model, object|string|array $payload, array $options = []): ResponseInterface
    {
        return $this->httpClient->request('POST', 'https://api.voyageai.com/v1/embeddings', [
            'auth_bearer' => $this->apiKey,
            'json' => [
                'model' => $model->getName(),
                'input' => $payload,
            ],
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        $response = $response->toArray();

        if (!isset($response['data'])) {
            throw new RuntimeException('Response does not contain embedding data');
        }

        $vectors = array_map(fn (array $data) => new Vector($data['embedding']), $response['data']);

        return new VectorResponse($vectors[0]);
    }
}
