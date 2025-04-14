<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\TransformersPHP;

use PhpLlm\LlmChain\Model\Model as BaseModel;

final readonly class Model implements BaseModel
{
    /**
     * @param string               $name    the name of the model is optional with TransformersPHP
     * @param array<string, mixed> $options
     */
    public function __construct(
        private ?string $name = null,
        private array $options = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name ?? '';
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}
