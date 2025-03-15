<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\Whisper;

use PhpLlm\LlmChain\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ResponseConverter as BaseResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class ResponseConverter implements BaseResponseConverter
{
    public function supports(Model $model, object|array|string $input): bool
    {
        return $model instanceof Whisper && $input instanceof File;
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        return new TextResponse($data['text']);
    }
}
