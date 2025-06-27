<?php

namespace PhpLlm\LlmChain\Platform\Bridge\Google\Embeddings;

use PhpLlm\LlmChain\Platform\Bridge\Google\Embeddings;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ModelClientInterface;
use PhpLlm\LlmChain\Platform\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Valtteri R <valtzu@gmail.com>
 */
final readonly class ModelClient implements ModelClientInterface, ResponseConverterInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[\SensitiveParameter]
        private string $apiKey,
    ) {
    }

    public function supports(Model $model): bool
    {
        return $model instanceof Embeddings;
    }

    public function request(Model $model, array|string $payload, array $options = []): ResponseInterface
    {
        $url = \sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:%s', $model->getName(), 'batchEmbedContents');
        $modelOptions = $model->getOptions();

        return $this->httpClient->request('POST', $url, [
            'headers' => [
                'x-goog-api-key' => $this->apiKey,
            ],
            'json' => [
                'requests' => array_map(
                    static fn (string $text) => array_filter([
                        'model' => 'models/'.$model->getName(),
                        'content' => ['parts' => [['text' => $text]]],
                        'outputDimensionality' => $modelOptions['dimensions'] ?? null,
                        'taskType' => $modelOptions['task_type'] ?? null,
                        'title' => $options['title'] ?? null,
                    ]),
                    \is_array($payload) ? $payload : [$payload],
                ),
            ],
        ]);
    }

    public function convert(ResponseInterface $response, array $options = []): VectorResponse
    {
        $data = $response->toArray();

        if (!isset($data['embeddings'])) {
            throw new RuntimeException('Response does not contain data');
        }

        return new VectorResponse(
            ...array_map(
                static fn (array $item): Vector => new Vector($item['values']),
                $data['embeddings'],
            ),
        );
    }
}
