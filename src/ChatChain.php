<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain;

use SymfonyLlm\LlmChain\Message\Message;
use SymfonyLlm\LlmChain\Message\MessageBag;
use SymfonyLlm\LlmChain\OpenAI\ChatModel;

final class ChatChain implements LlmChainInterface
{
    public function __construct(private ChatModel $model)
    {
    }

    public function call(Message $message, MessageBag $messages): string
    {
        $response = $this->model->call($messages->with($message));

        return $response['choices'][0]['message']['content'];
    }
}
