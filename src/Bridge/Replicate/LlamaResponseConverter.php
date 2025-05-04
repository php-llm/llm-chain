<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Replicate;

use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final readonly class LlamaResponseConverter implements ResponseConverter
{
    public function supports(Model $model): bool
    {
        return $model instanceof Llama;
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        if (!isset($data['output'])) {
            throw new RuntimeException('Response does not contain output');
        }

        return new TextResponse(implode('', $data['output']));
    }
}
