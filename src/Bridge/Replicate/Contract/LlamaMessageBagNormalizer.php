<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Replicate\Contract;

use PhpLlm\LlmChain\Bridge\Meta\Llama;
use PhpLlm\LlmChain\Bridge\Meta\LlamaPromptConverter;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Contract;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class LlamaMessageBagNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly LlamaPromptConverter $promptConverter = new LlamaPromptConverter(),
    ) {
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof MessageBagInterface
            && isset($context[Contract::CONTEXT_MODEL])
            && $context[Contract::CONTEXT_MODEL] instanceof Llama;
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
     * @return array{system: string, prompt: string}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'system' => $this->promptConverter->convertMessage($data->getSystemMessage() ?? new SystemMessage('')),
            'prompt' => $this->promptConverter->convertToPrompt($data->withoutSystemMessage()),
        ];
    }
}
