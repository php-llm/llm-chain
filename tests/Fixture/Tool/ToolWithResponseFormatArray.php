<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\ToolBox\Attribute\AsTool;

#[AsTool(
    name: 'tool_with_response_format_array',
    description: 'A tool with response format array',
    responseFormat: [
        'type' => 'object',
        'properties' => [
            'message' => [
                'type' => 'string',
            ],
        ],
        'required' => ['message'],
        'additionalProperties' => false,
    ]
)]
final class ToolWithResponseFormatArray
{
    /**
     * @param string $text The text given to the tool
     */
    public function __invoke(string $text): string
    {
        return sprintf('Text: %s', $text);
    }
}
