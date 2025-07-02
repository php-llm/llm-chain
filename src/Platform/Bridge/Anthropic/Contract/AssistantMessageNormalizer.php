<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Anthropic\Claude;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
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
        return $model instanceof Claude;
    }

    /**
     * @param AssistantMessage $data
     *
     * @return array{
     *     role: 'assistant',
     *     content: list<array{
     *         type: 'tool_use',
     *         id: string,
     *         name: string,
     *         input: array<string, mixed>
     *     }>
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return [
            'role' => 'assistant',
            'content' => array_map(static function (ToolCall $toolCall) {
                return [
                    'type' => 'tool_use',
                    'id' => $toolCall->id,
                    'name' => $toolCall->name,
                    'input' => 0 !== \count($toolCall->arguments) ? $toolCall->arguments : new \stdClass(),
                ];
            }, $data->toolCalls),
        ];
    }
}
