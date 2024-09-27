<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\ResponseInterface;

final readonly class Output
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public LanguageModel $llm,
        public ResponseInterface $response,
        public MessageBag $messages,
        public array $options,
    ) {
    }
}
