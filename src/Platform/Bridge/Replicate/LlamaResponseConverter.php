<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Replicate;

use PhpLlm\LlmChain\Platform\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final readonly class LlamaResponseConverter implements ResponseConverterInterface
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
