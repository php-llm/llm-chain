<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Vector;

use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\OpenAI\Embeddings;
use PhpLlm\LlmChain\RetrieverInterface;

final class Retriever implements RetrieverInterface
{
    public function __construct(
        private Embeddings $embeddings,
        private Pinecone $client,
    ) {
    }

    public function enrich(Message $message): Message
    {
        $vector = $this->embeddings->create($message->content ?? '');
        $hits = $this->client->query($vector);

        $prompt = <<<PROMPT
            Use the following information or information from previous messages to answer the question at the very end.
            Do not add any information and if you cannot find an answer, say so.
            PROMPT;

        foreach ($hits as $hit) {
            foreach ($hit['metadata'] as $key => $value) {
                $prompt .= ucfirst($key).': '.$value.', ';
            }
            $prompt .= PHP_EOL;
        }

        $prompt .= '. Question: '.$message->content;

        return Message::ofUser($prompt);
    }
}
