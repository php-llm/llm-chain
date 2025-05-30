<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AssistantMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AssistantMessage;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AssistantMessage::class => true,
        ];
    }

    /**
     * @param AssistantMessage $data
     *
     * @return array{role: 'assistant', content: string}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $array = [
            'role' => $data->getRole()->value,
        ];

        if (null !== $data->content) {
            $array['content'] = $data->content;
        }

        if ($data->hasToolCalls()) {
            $array['tool_calls'] = $this->normalizer->normalize($data->toolCalls, $format, $context);
        }

        return $array;
    }
}
