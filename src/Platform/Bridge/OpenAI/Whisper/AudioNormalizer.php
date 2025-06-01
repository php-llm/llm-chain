<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Message\Content\Audio;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AudioNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Audio && $context[Contract::CONTEXT_MODEL] instanceof Whisper;
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
     * @return array{model: string, file: resource}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'model' => $context[Contract::CONTEXT_MODEL]->getName(),
            'file' => $data->asResource(),
        ];
    }
}
