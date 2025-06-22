<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Denormalizer;

use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\StreamResponse;
use PhpLlm\LlmChain\Platform\ResponseContract;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class StreamResponseDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): LlmResponse
    {
        $response = $context[ResponseContract::CONTEXT_HTTP_RESPONSE];

        return new StreamResponse($this->createStreamGenerator($response, $context));
    }

    /**
     * @param array<string, mixed> $context
     */
    private function createStreamGenerator(ResponseInterface $response, array $context): \Generator
    {
        $toolCalls = [];
        $textBuffer = '';

        try {
            foreach ((new EventSourceHttpClient())->stream($response) as $chunk) {
                if (!$chunk instanceof ServerSentEvent) {
                    continue;
                }

                if ('[DONE]' === $chunk->getData()) {
                    break;
                }

                try {
                    $data = $chunk->getArrayData();
                } catch (\JsonException) {
                    continue;
                }

                /** @var array{textDelta: ?string, toolCallDeltas: array<int, array{id?: string, name?: string, arguments?: string}>, finishReason: ?string, isDone: bool} $streamChunk */
                $streamChunk = $this->denormalizer->denormalize(
                    $data,
                    'stream_chunk',
                    null,
                    $context
                );

                if ($streamChunk['textDelta']) {
                    $textBuffer .= $streamChunk['textDelta'];
                    yield $streamChunk['textDelta'];
                }

                $toolCalls = $this->accumulateToolCalls($toolCalls, $streamChunk['toolCallDeltas']);

                if ($streamChunk['isDone']) {
                    break;
                }
            }
        } catch (HttpExceptionInterface) {
            // Stream ended or error occurred
        }
    }

    /**
     * @param array<int, array{id?: string, name?: string, arguments?: string}> $toolCalls
     * @param array<int, array{id?: string, name?: string, arguments?: string}> $deltas
     *
     * @return array<int, array{id?: string, name?: string, arguments?: string}>
     */
    private function accumulateToolCalls(array $toolCalls, array $deltas): array
    {
        foreach ($deltas as $i => $delta) {
            if (isset($delta['id'])) {
                // Initialize tool call
                $toolCalls[$i] = [
                    'id' => $delta['id'],
                    'name' => $delta['name'] ?? '',
                    'arguments' => $delta['arguments'] ?? '',
                ];
                continue;
            }

            // Add arguments delta to tool call
            if (isset($toolCalls[$i]) && isset($delta['arguments'])) {
                $toolCalls[$i]['arguments'] .= $delta['arguments'];
            }
        }

        return $toolCalls;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return LlmResponse::class === $type && ($context[ResponseContract::CONTEXT_OPTIONS]['stream'] ?? false);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [LlmResponse::class => false];
    }
}
