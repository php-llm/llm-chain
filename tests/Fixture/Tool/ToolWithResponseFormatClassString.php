<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Tests\Fixture\FaqItem;
use PhpLlm\LlmChain\ToolBox\Attribute\AsTool;

#[AsTool(
    name: 'tool_with_response_format_class_string',
    description: 'A tool with response format class-string',
    responseFormat: FaqItem::class
)]
final class ToolWithResponseFormatClassString
{
    /**
     * @param string $text The text given to the tool
     */
    public function __invoke(string $text): string
    {
        return sprintf('Text: %s', $text);
    }
}
