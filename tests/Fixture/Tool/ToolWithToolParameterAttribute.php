<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\ToolBox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\ToolBox\Attribute\With;

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
        #[With(enum: ['dog', 'cat', 'bird'])]
        string $animal,
        #[With(const: 42)]
        int $numberOfArticles,
        #[With(const: 'info@example.de')]
        string $infoEmail,
        #[With(const: ['de', 'en'])]
        string $locales,
        #[With(
            pattern: '^[a-zA-Z]+$',
            minLength: 1,
            maxLength: 10,
        )]
        string $text,
        #[With(
            minimum: 1,
            maximum: 10,
            multipleOf: 2,
            exclusiveMinimum: 1,
            exclusiveMaximum: 10,
        )]
        int $number,
        #[With(
            minItems: 1,
            maxItems: 10,
            uniqueItems: true,
            minContains: 1,
            maxContains: 10,
        )]
        array $products,
        #[With(
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
