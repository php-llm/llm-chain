<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model;

interface Model
{
    public function getVersion(): string;

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array;
}
