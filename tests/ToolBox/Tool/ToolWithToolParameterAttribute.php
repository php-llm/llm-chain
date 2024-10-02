<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\ToolBox\Tool;

use PhpLlm\LlmChain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\ToolBox\Attribute\ToolParameter;

#[AsTool('tool_with_ToolParameter_attribute', 'A tool which has a parameter with described with #[ToolParameter] attribute')]
final class ToolWithToolParameterAttribute
{
    /**
     * @param string $animal           The animal given to the tool
     * @param int    $numberOfArticles The number of articles given to the tool
     * @param string $infoEmail        The info email given to the tool
     * @param string $locales          The locales given to the tool
     * @param string $text             The text given to the tool
     * @param int    $number           The number given to the tool
     * @param array  $products         The products given to the tool
     * @param object $shippingAddress  The shipping address given to the tool
     */
    public function __invoke(
        #[ToolParameter(
            enum: ['dog', 'cat', 'bird'],
        )]
        string $animal,
        #[ToolParameter(
            const: 42,
        )]
        int $numberOfArticles,
        #[ToolParameter(
            const: 'info@example.de',
        )]
        string $infoEmail,
        #[ToolParameter(
            const: ['de', 'en'],
        )]
        string $locales,
        #[ToolParameter(
            pattern: '^[a-zA-Z]+$',
            minLength: 1,
            maxLength: 10,
        )]
        string $text,
        #[ToolParameter(
            minimum: 1,
            maximum: 10,
            multipleOf: 2,
            exclusiveMinimum: 1,
            exclusiveMaximum: 10,
        )]
        int $number,
        #[ToolParameter(
            minItems: 1,
            maxItems: 10,
            uniqueItems: true,
            minContains: 1,
            maxContains: 10,
        )]
        array $products,
        #[ToolParameter(
            required: true,
            minProperties: 1,
            maxProperties: 10,
            dependentRequired: true,
        )]
        object $shippingAddress,
    ): string {
        return 'Hello, World!';
    }
}
