<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;

#[AsTool('tool_returning_json_serializable', 'A tool returning an object which implements \JsonSerializable')]
final class ToolReturningJsonSerializable
{
    public function __invoke(): \JsonSerializable
    {
        return new class implements \JsonSerializable {
            public function jsonSerialize(): array
            {
                return ['foo' => 'bar'];
            }
        };
    }
}
