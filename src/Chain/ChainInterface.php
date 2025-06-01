<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;

interface ChainInterface
{
    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBagInterface $messages, array $options = []): ResponseInterface;
}
