<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI;

use PhpLlm\LlmChain\Message\MessageBag;

final class ChatModel
{
    public function __construct(
        private OpenAIClientInterface $client,
        private string $model = 'gpt-4o',
        private float $temperature = 1.0,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function call(MessageBag $messages, array $options = []): array
    {
        $body = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
        ];

        return $this->client->request('chat/completions', array_merge($body, $options));
    }
}
