<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Event;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;

final class InputEvent
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public readonly LanguageModel $llm,
        public readonly MessageBag $messages,
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
