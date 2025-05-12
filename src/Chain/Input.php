<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain;

use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Model;

final class Input
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public Model $model,
        public MessageBagInterface $messages,
        private array $options,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
