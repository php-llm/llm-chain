<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Response;

interface ResponseInterface
{
    /**
     * @return Choice[]
     */
    public function getChoices(): array;

    /**
     * @return string|iterable<string>|null
     */
    public function getContent(): string|iterable|null;

    /**
     * @return ToolCall[]
     */
    public function getToolCalls(): array;

    public function hasToolCalls(): bool;
}
