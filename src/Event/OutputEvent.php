<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Event;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\ResponseInterface;

final class OutputEvent
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly LanguageModel $llm,
        public ResponseInterface $response,
        public readonly MessageBag $messages,
        public readonly array $options,
    ) {
    }
}
