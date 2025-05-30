<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ToolCallMessageNormalizer extends ModelContractNormalizer implements NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected function supportedDataClass(): string
    {
        return ToolCallMessage::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Claude;
    }

    /**
     * @param ToolCallMessage $data
     *
     * @return array{
     *     role: 'user',
     *     content: list<array{
     *         type: 'tool_result',
     *         tool_use_id: string,
     *         content: string,
     *     }>
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'tool_result',
                    'tool_use_id' => $data->toolCall->id,
                    'content' => $data->content,
                ],
            ],
        ];
    }
}
