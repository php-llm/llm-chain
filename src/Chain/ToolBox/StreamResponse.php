<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;

final readonly class StreamResponse implements ResponseInterface
{
    public function __construct(
        private \Generator $generator,
        private \Closure $handleToolCallsCallback,
    ) {
    }

    public function getContent(): \Generator
    {
        $streamedResponse = '';
        foreach ($this->generator as $value) {
            if ($value instanceof ToolCallResponse) {
                yield from ($this->handleToolCallsCallback)($value, Message::ofAssistant($streamedResponse))->getContent();

                break;
            }

            $streamedResponse .= $value;
            yield $value;
        }
    }
}
