<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content;

use PhpLlm\LlmChain\Platform\Message\Content\Audio;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class AudioNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Audio;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Audio::class => true,
        ];
    }

    /**
     * @param Audio $data
     *
     * @return array{type: 'input_audio', input_audio: array{
     *     data: string,
     *     format: 'mp3'|'wav'|string,
     * }}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'type' => 'input_audio',
            'input_audio' => [
                'data' => $data->asBase64(),
                'format' => match ($data->getFormat()) {
                    'audio/mpeg' => 'mp3',
                    'audio/wav' => 'wav',
                    default => $data->getFormat(),
                },
            ],
        ];
    }
}
