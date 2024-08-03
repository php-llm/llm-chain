<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;

final class ChatChain implements LlmChainInterface
{
    public function __construct(
        private ChatInterface $chat,
    ) {
    }

    public function call(Message $message, MessageBag $messages, array $options = []): string
    {
        $response = $this->chat->call($messages->with($message), $options);

        // OpenAI GPT
        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }

        // Anthropic Claude
        if (isset($response['content'][0]['text'])) {
            return $response['content'][0]['text'];
        }

        throw new \Exception('Unknown response format');
    }
}
