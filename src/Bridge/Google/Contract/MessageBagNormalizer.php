<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google\Contract;

use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\Role;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MessageBagNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof MessageBagInterface;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MessageBagInterface::class => true,
        ];
    }

    /**
     * @param MessageBagInterface $data
     *
     * @return array{
     *      contents: list<array{
     *          role: 'model'|'user',
     *          parts: array<int, mixed>
     *      }>,
     *      system_instruction?: array{parts: array{text: string}}
     *  }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $array = ['contents' => []];

        if (null !== $systemMessage = $data->getSystemMessage()) {
            $array['system_instruction'] = [
                'parts' => ['text' => $systemMessage->content],
            ];
        }

        foreach ($data->withoutSystemMessage()->getMessages() as $message) {
            $array['contents'][] = [
                'role' => $message->getRole()->equals(Role::Assistant) ? 'model' : 'user',
                'parts' => $this->normalizer->normalize($message, $format, $context),
            ];
        }

        return $array;
    }
}
