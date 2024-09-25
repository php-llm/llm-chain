<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\Response;

final readonly class Response
{
    /**
     * @var Choice[]
     */
    private array $choices;

    public function __construct(Choice ...$choice)
    {
        if (0 === count($choice)) {
            throw new \InvalidArgumentException('Response must have at least one choice');
        }

        $this->choices = $choice;
    }

    /**
     * @return Choice[]
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    public function getContent(): ?string
    {
        if (1 < count($this->choices)) {
            throw new \LogicException('Response has more than one choice');
        }

        return $this->choices[0]->getContent();
    }

    /**
     * @return ToolCall[]
     */
    public function getToolCalls(): array
    {
        if (1 < count($this->choices)) {
            throw new \LogicException('Response has more than one choice');
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
