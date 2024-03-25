<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;

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
