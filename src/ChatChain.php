<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\OpenAI\ChatModel;

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
