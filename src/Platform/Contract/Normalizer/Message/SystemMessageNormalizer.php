<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SystemMessageNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SystemMessage;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            SystemMessage::class => true,
        ];
    }

    /**
     * @param SystemMessage $data
     *
     * @return array{role: 'system', content: string}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'role' => $data->getRole()->value,
            'content' => $data->content,
        ];
    }
}
