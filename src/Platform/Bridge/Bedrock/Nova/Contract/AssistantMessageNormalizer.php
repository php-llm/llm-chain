<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Nova;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ToolCall;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class AssistantMessageNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return AssistantMessage::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Nova;
    }

    /**
     * @param AssistantMessage $data
     *
     * @return array{
     *     role: 'assistant',
     *     content: array<array{
     *         toolUse?: array{
     *             toolUseId: string,
     *             name: string,
     *             input: mixed,
     *         },
     *         text?: string,
     *     }>
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if ($data->hasToolCalls()) {
            return [
                'role' => 'assistant',
                'content' => array_map(static function (ToolCall $toolCall) {
                    return [
                        'toolUse' => [
                            'toolUseId' => $toolCall->id,
                            'name' => $toolCall->name,
                            'input' => 0 !== \count($toolCall->arguments) ? $toolCall->arguments : new \stdClass(),
                        ],
                    ];
                }, $data->toolCalls),
            ];
        }

        return [
            'role' => 'assistant',
            'content' => [['text' => $data->content]],
        ];
    }
}
