<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Response\BaseResponse;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;

final class StreamResponse extends BaseResponse
{
    public function __construct(
        private readonly \Generator $generator,
        private readonly \Closure $handleToolCallsCallback,
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
