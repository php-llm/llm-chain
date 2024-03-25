<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\ChatModel;
use PhpLlm\LlmChain\ToolBox\Registry;

final class ToolChain implements LlmChainInterface
{
    public function __construct(
        private ChatModel $model,
        private Registry $toolRegistry,
    ) {
    }

    public function call(Message $message, MessageBag $messages): string
    {
        $messages[] = $message;

        $toolMap = $this->toolRegistry->getMap();
        $response = $this->model->call($messages, ['tools' => $toolMap]);

        while ('tool_calls' === $response['choices'][0]['finish_reason']) {
            ['name' => $name, 'arguments' => $arguments] = $response['choices'][0]['message']['tool_calls'][0]['function'];
            $result = $this->toolRegistry->execute($name, $arguments);

            $messages[] = Message::ofAssistant(functionCall: [
                'name' => $name,
                'arguments' => $arguments,
            ]);
            $messages[] = Message::ofFunctionCall($name, $result);

            $response = $this->model->call($messages, ['tools' => $toolMap]);
        }

        return $response['choices'][0]['message']['content'];
    }
}
