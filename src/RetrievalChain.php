<?php

declare(strict_types=1);

namespace SymfonyLlm\LlmChain;

use SymfonyLlm\LlmChain\Message\Message;
use SymfonyLlm\LlmChain\Message\MessageBag;

final class RetrievalChain implements LlmChainInterface
{
    public function __construct(
        private RetrieverInterface $retriever,
        private LlmChainInterface $chain,
    ) {
    }

    public function call(Message $message, MessageBag $messages): string
    {
        $retrievalPrompt = $this->retriever->enrich($message);

        return $this->chain->call($retrievalPrompt, $messages);
    }
}
