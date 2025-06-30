<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Chain\Chat\MessageStoreInterface;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Response\TextResponse;

final readonly class Chat implements ChatInterface
{
    public function __construct(
        private ChainInterface $chain,
        private MessageStoreInterface $store,
    ) {
    }

    public function initiate(MessageBagInterface $messages): void
    {
        $this->store->clear();
        $this->store->save($messages);
    }

    public function submit(UserMessage $message): AssistantMessage
    {
        $messages = $this->store->load();

        $messages->add($message);
        $response = $this->chain->call($messages);

        \assert($response instanceof TextResponse);

        $assistantMessage = Message::ofAssistant($response->getContent());
        $messages->add($assistantMessage);

        $this->store->save($messages);

        return $assistantMessage;
    }
}
