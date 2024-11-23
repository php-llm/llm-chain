<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings;
use PhpLlm\LlmChain\Document\Vector;
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

        return new VectorResponse(new Vector($data['data'][0]['embedding']));
    }
}
