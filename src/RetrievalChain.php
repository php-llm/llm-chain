<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;

final class RetrievalChain
{
    public function __construct(
        private RetrieverInterface $retriever,
        private LlmChainInterface $chain,
    ) {
    }

    public function call(Message $message, MessageBag $messages, array $options = []): string
    {
        $retrievalString = $this->retriever->enrich($message->content);
        $retrievalPrompt = <<<PROMPT
            Use the following information or information from previous system and assistant messages to answer the
            question at the very end. Do not add any information and if you cannot find an answer, say so.
            
            Information:
            {$retrievalString}
            
            Question: {$message->content}
            PROMPT;

        return $this->chain->call(Message::ofUser($retrievalPrompt), $messages, $options);
    }
}
