<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\OpenAI\ResponseContract;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Contract\Denormalizer\ModelContractDenormalizer;
use PhpLlm\LlmChain\Platform\Model;

final class OpenAIStreamParser extends ModelContractDenormalizer
{
    protected function supportsModel(Model $model): bool
    {
        return $model instanceof GPT;
    }

    /**
     * @return array{textDelta: ?string, toolCallDeltas: array<int, array{id?: string, name?: string, arguments?: string}>, finishReason: ?string, isDone: bool}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): array
    {
        $choice = $data['choices'][0] ?? [];
        $delta = $choice['delta'] ?? [];

        return [
            'textDelta' => $delta['content'] ?? null,
            'toolCallDeltas' => $this->parseToolCallDeltas($delta['tool_calls'] ?? []),
            'finishReason' => $choice['finish_reason'] ?? null,
            'isDone' => false, // OpenAI uses '[DONE]' signal which is handled by the stream denormalizer
        ];
    }

    /**
     * @param array<int, array{id?: string, function?: array{name?: string, arguments?: string}}> $toolCalls
     *
     * @return array<int, array{id?: string, name?: string, arguments?: string}>
     */
    private function parseToolCallDeltas(array $toolCalls): array
    {
        $deltas = [];

        foreach ($toolCalls as $i => $toolCall) {
            $delta = [];

            if (isset($toolCall['id'])) {
                $delta['id'] = $toolCall['id'];
            }

            if (isset($toolCall['function']['name'])) {
                $delta['name'] = $toolCall['function']['name'];
            }

            if (isset($toolCall['function']['arguments'])) {
                $delta['arguments'] = $toolCall['function']['arguments'];
            }

            if (!empty($delta)) {
                $deltas[$i] = $delta;
            }
        }

        return $deltas;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return 'stream_chunk' === $type && parent::supportsDenormalization($data, $type, $format, $context);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['stream_chunk' => false];
    }
}
