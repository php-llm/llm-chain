<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Response;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Exception\LogicException;

final readonly class Response implements ResponseInterface
{
    /**
     * @var Choice[]
     */
    private array $choices;

    public function __construct(Choice ...$choice)
    {
        if (0 === count($choice)) {
            throw new InvalidArgumentException('Response must have at least one choice');
        }

        $this->choices = $choice;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function getContent(): ?string
    {
        if (1 < count($this->choices)) {
            throw new LogicException('Response has more than one choice');
        }

        return $this->choices[0]->getContent();
    }

    public function getToolCalls(): array
    {
        if (1 < count($this->choices)) {
            throw new LogicException('Response has more than one choice');
        }

        return $this->choices[0]->getToolCalls();
    }

    public function hasToolCalls(): bool
    {
        foreach ($this->choices as $choice) {
            if ($choice->hasToolCall()) {
                return true;
            }
        }

        return false;
    }
}
