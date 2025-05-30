<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Nova;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class MessageBagNormalizer extends ModelContractNormalizer implements NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected function supportedDataClass(): string
    {
        return MessageBagInterface::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Nova;
    }

    /**
     * @param MessageBagInterface $data
     *
     * @return array{
     *     messages: array<array<string, mixed>>,
     *     system?: array<array{text: string}>,
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $array = [];

        if ($data->getSystemMessage()) {
            $array['system'][]['text'] = $data->getSystemMessage()->content;
        }

        $array['messages'] = $this->normalizer->normalize($data->withoutSystemMessage()->getMessages(), $format, $context);

        return $array;
    }
}
