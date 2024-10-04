<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Store\Azure;

use PhpLlm\LlmChain\Document\Metadata;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Document\VectorDocument;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Store\VectorStoreInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class SearchStore implements VectorStoreInterface
{
    /**
     * @param string $vectorFieldName The name of the field int the index that contains the vector
     */
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $endpointUrl,
        #[\SensitiveParameter] private string $apiKey,
        private string $indexName,
        private string $apiVersion,
        private string $vectorFieldName = 'vector',
    ) {
    }

    public function add(VectorDocument ...$documents): void
    {
        $this->request('index', [
            'value' => array_map([$this, 'convertToIndexableArray'], $documents),
        ]);
    }

    public function query(Vector $vector, array $options = []): array
    {
        $result = $this->request('search', [
            'vectorQueries' => [$this->buildVectorQuery($vector)],
        ]);

        return array_map([$this, 'convertToVectorDocument'], $result['value']);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function request(string $endpoint, array $payload): array
    {
        $url = sprintf('%s/indexes/%s/docs/%s', $this->endpointUrl, $this->indexName, $endpoint);
        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'query' => ['api-version' => $this->apiVersion],
            'json' => $payload,
        ]);

        return $response->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function convertToIndexableArray(VectorDocument $document): array
    {
        return array_merge([
            'id' => $document->id,
            $this->vectorFieldName => $document->vector->getData(),
        ], $document->metadata->getArrayCopy());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function convertToVectorDocument(array $data): VectorDocument
    {
        if (!array_key_exists($this->vectorFieldName, $data)) {
            throw new RuntimeException(sprintf('The field "%s" is missing in the search result. This mostly happens if the index was not populated by this library. Please make sure to add this information to your index.', $this->vectorFieldName));
        }

        return new VectorDocument(
            id: Uuid::fromString($data['id']),
            vector: new Vector($data[$this->vectorFieldName]),
            metadata: new Metadata($data),
        );
    }

    /**
     * @return array{
     *     kind: 'vector',
     *     vector: float[],
     *     exhaustive: true,
     *     fields: non-empty-string,
     *     weight: float,
     *     k: int,
     * }
     */
    private function buildVectorQuery(Vector $vector): array
    {
        return [
            'kind' => 'vector',
            'vector' => $vector->getData(),
            'exhaustive' => true,
            'fields' => $this->vectorFieldName,
            'weight' => 0.5,
            'k' => 5,
        ];
    }
}
