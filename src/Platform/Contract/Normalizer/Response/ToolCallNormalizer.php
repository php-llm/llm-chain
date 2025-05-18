<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Response;

use PhpLlm\LlmChain\Model\Response\ToolCall;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ToolCallNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ToolCall;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ToolCall::class => true,
        ];
    }

    /**
     * @param ToolCall $data
     *
     * @return array{
     *      id: string,
     *      type: 'function',
     *      function: array{
     *          name: string,
     *          arguments: string
     *      }
     *  }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'id' => $data->id,
            'type' => 'function',
            'function' => [
                'name' => $data->name,
                'arguments' => json_encode($data->arguments),
            ],
        ];
    }
}
