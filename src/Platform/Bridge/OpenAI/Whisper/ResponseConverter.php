<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface as BaseResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ResponseConverter implements BaseResponseConverter
{
    public function supports(Model $model): bool
    {
        return $model instanceof Whisper;
    }

    public function convert(HttpResponse $response, array $options = []): LlmResponse
    {
        $data = $response->toArray();

        return new TextResponse($data['text']);
    }
}
