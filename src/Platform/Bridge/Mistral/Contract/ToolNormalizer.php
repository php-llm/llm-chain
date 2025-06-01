<?php

namespace PhpLlm\LlmChain\Platform\Bridge\Mistral\Contract;

use PhpLlm\LlmChain\Platform\Contract\Normalizer\ToolNormalizer as BaseToolNormalizer;

class ToolNormalizer extends BaseToolNormalizer
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $array = parent::normalize($data, $format, $context);

        $array['function']['parameters'] ??= ['type' => 'object'];

        return $array;
    }
}
