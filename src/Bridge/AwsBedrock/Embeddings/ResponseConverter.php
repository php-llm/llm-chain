<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\AwsBedrock\Embeddings;

use PhpLlm\LlmChain\Bridge\AwsBedrock\Embeddings;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ResponseConverter as PlatformResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ResponseConverter implements PlatformResponseConverter
{
    public function supports(Model $model, array|string|object $input): bool
    {
        return $model instanceof Embeddings;
    }

    public function convert(ResponseInterface $response, array $options = []): VectorResponse
    {
        $data = $response->toArray();

        if (!isset($data['embedding'])) {
            throw new RuntimeException('Response does not contain data');
        }

        return new VectorResponse(
            new Vector($data['embedding'])
        );
    }
}
