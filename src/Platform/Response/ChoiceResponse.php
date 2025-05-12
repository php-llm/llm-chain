<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Exception\InvalidArgumentException;

final class ChoiceResponse extends BaseResponse
{
    /**
     * @var Choice[]
     */
    private readonly array $choices;

    public function __construct(Choice ...$choices)
    {
        if (0 === \count($choices)) {
            throw new InvalidArgumentException('Response must have at least one choice.');
        }

        $this->choices = $choices;
    }

    /**
     * @return Choice[]
     */
    public function getContent(): array
    {
        return $this->choices;
    }
}
