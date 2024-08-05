<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\ToolBox\Registry;

final class ToolChain
{
    public function __construct(
        private LanguageModel $llm,
        private Registry $toolRegistry,
    ) {
    }

    public function call(MessageBag $messages, array $options = []): string
    {
        $options['tools'] = $this->toolRegistry->getMap();
        $clonedMessages = clone $messages;
        $response = $this->llm->call($messages, $options);

        while ('tool_calls' === $response['choices'][0]['finish_reason']) {
            foreach ($response['choices'][0]['message']['tool_calls'] as $toolCall) {
                ['name' => $name, 'arguments' => $arguments] = $toolCall['function'];
                $result = $this->toolRegistry->execute($name, $arguments);

                $clonedMessages[] = Message::ofAssistant(functionCall: [
                    'name' => $name,
                    'arguments' => $arguments,
                ]);
                $clonedMessages[] = Message::ofFunctionCall($name, $result);
            }

            $response = $this->llm->call($clonedMessages, $options);
        }

        return $response['choices'][0]['message']['content'];
    }
}
