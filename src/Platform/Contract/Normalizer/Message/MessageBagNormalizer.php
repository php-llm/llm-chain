<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
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
     *     messages: array<string, mixed>,
     *     model?: string,
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $array = [
            'messages' => $this->normalizer->normalize($data->getMessages(), $format, $context),
        ];

        if (isset($context[Contract::CONTEXT_MODEL]) && $context[Contract::CONTEXT_MODEL] instanceof Model) {
            $array['model'] = $context[Contract::CONTEXT_MODEL]->getName();
        }

        return $array;
    }
}
