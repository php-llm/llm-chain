<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox\Event;

use PhpLlm\LlmChain\Chain\Toolbox\ToolCallResult;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ToolCallsExecuted
{
    /**
     * @var ToolCallResult[]
     */
    public readonly array $toolCallResults;
    public ResponseInterface $response;

    public function __construct(ToolCallResult ...$toolCallResults)
    {
        $this->toolCallResults = $toolCallResults;
    }

    public function hasResponse(): bool
    {
        return isset($this->response);
    }
}
