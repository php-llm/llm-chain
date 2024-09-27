<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\ResponseInterface;

interface LanguageModel
{
    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBag $messages, array $options = []): ResponseInterface;

    public function supportsToolCalling(): bool;

    public function supportsImageInput(): bool;

    public function supportsStructuredOutput(): bool;
}
