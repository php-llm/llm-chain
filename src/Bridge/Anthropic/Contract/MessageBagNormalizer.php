<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\Contract;
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
     *     messages: array<string, mixed>,
     *     model?: string,
     *     system?: string,
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $array = [
            'messages' => $this->normalizer->normalize($data->withoutSystemMessage()->getMessages(), $format, $context),
        ];

        if (null !== $system = $data->getSystemMessage()) {
            $array['system'] = $system->content;
        }

        if (isset($context[Contract::CONTEXT_MODEL]) && $context[Contract::CONTEXT_MODEL] instanceof Model) {
            $array['model'] = $context[Contract::CONTEXT_MODEL]->getName();
        }

        return $array;
    }
}
