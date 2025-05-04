<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content;

use PhpLlm\LlmChain\Model\Message\Content\Text;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TextNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Text;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Text::class => true,
        ];
    }

    /**
     * @param Text $data
     *
     * @return array{type: 'text', text: string}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return ['type' => 'text', 'text' => $data->text];
    }
}
