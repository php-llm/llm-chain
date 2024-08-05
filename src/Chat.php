<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\MessageBag;

final class Chat
{
    public function __construct(
        private LanguageModel $llm,
    ) {
    }

    public function call(MessageBag $messages, array $options = []): string
    {
        $response = $this->llm->call($messages, $options);

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
