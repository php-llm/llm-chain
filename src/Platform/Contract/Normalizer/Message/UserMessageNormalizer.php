<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class UserMessageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UserMessage;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            UserMessage::class => true,
        ];
    }

    /**
     * @param UserMessage $data
     *
     * @return array{role: 'assistant', content: string}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $array = ['role' => $data->getRole()->value];

        if (1 === count($data->content) && $data->content[0] instanceof Text) {
            $array['content'] = $data->content[0]->text;

            return $array;
        }

        $array['content'] = $this->normalizer->normalize($data->content, $format, $context);

        return $array;
    }
}
