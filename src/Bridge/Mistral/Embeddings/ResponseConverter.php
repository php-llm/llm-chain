<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Mistral\Embeddings;

use PhpLlm\LlmChain\Bridge\Mistral\Embeddings;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ResponseConverter as PlatformResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class ResponseConverter implements PlatformResponseConverter
{
    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Embeddings;
    }

    public function convert(ResponseInterface $response, array $options = []): VectorResponse
    {
        $data = $response->toArray(false);

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException(sprintf('Unexpected response code %d: %s', $response->getStatusCode(), $response->getContent(false)));
        }

        if (!isset($data['data'])) {
            throw new RuntimeException('Response does not contain data');
        }

        return new VectorResponse(
            ...\array_map(
                static fn (array $item): Vector => new Vector($item['embedding']),
                $data['data']
            ),
        );
    }
}
