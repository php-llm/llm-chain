<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;

final class Output
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly Model $model,
        public ResponseInterface $response,
        public readonly MessageBagInterface $messages,
        public readonly array $options,
    ) {
    }
}
