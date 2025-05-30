<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface as PlatformResponseConverter;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ResponseConverter implements PlatformResponseConverter
{
    public function supports(Model $model): bool
    {
        return $model instanceof Embeddings;
    }

    public function convert(ResponseInterface $response, array $options = []): VectorResponse
    {
        $data = $response->toArray();

        if (!isset($data['data'])) {
            throw new RuntimeException('Response does not contain data');
        }

        return new VectorResponse(
            ...array_map(
                static fn (array $item): Vector => new Vector($item['embedding']),
                $data['data']
            ),
        );
    }
}
