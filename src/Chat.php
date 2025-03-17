<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Chat\MessageStoreInterface;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Response\TextResponse;

final readonly class Chat implements ChatInterface
{
    public function __construct(
        private ChainInterface $chain,
        private MessageStoreInterface $store,
    ) {
    }

    public function start(MessageBag $messages): void
    {
        $this->store->clear();
        $this->store->save($messages);
    }

    public function submit(UserMessage $message): AssistantMessage
    {
        $messages = $this->store->load();

        $messages->add($message);
        $response = $this->chain->call($messages);

        assert($response instanceof TextResponse);

        $assistantMessage = Message::ofAssistant($response->getContent());
        $messages->add($assistantMessage);

        $this->store->save($messages);

        return $assistantMessage;
    }
}
