<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class MessageBagNormalizer extends ModelContractNormalizer implements NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected function supportedDataClass(): string
    {
        return MessageBagInterface::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Claude;
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
