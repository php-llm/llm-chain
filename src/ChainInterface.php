<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;

interface ChainInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBag $messages, array $options = []): ResponseInterface;
}
