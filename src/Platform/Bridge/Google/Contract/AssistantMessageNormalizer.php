<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Google\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class AssistantMessageNormalizer extends ModelContractNormalizer implements NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected function supportedDataClass(): string
    {
        return AssistantMessage::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Gemini;
    }

    /**
     * @param AssistantMessage $data
     *
     * @return array{array{text: string}}
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $normalized = [];

        if (isset($data->content)) {
            $normalized['text'] = $data->content;
        }

        if (isset($data->toolCalls[0])) {
            $normalized['functionCall'] = [
                'id' => $data->toolCalls[0]->id,
                'name' => $data->toolCalls[0]->name,
            ];

            if ($data->toolCalls[0]->arguments) {
                $normalized['functionCall']['args'] = $data->toolCalls[0]->arguments;
            }
        }

        return [$normalized];
    }
}
