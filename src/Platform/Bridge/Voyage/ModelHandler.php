<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Voyage;

use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class ModelHandler implements ModelClientInterface, ResponseConverterInterface
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
