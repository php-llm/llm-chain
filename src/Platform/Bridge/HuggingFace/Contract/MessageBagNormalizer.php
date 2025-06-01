<?php

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Contract;

use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
class MessageBagNormalizer extends ModelContractNormalizer implements NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected function supportedDataClass(): string
    {
        return MessageBagInterface::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return true;
    }

    /**
     * @param MessageBagInterface $data
     *
     * @return array{
     *     headers: array<'Content-Type', 'application/json'>,
     *     json: array{messages: array<string, mixed>}
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'messages' => $this->normalizer->normalize($data->getMessages(), $format, $context),
            ],
        ];
    }
}
