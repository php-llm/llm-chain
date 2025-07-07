<?php

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\Toolbox\Security\Attribute\IsGrantedTool;
use Symfony\Component\ExpressionLanguage\Expression;

#[IsGrantedTool('test:permission', new Expression('args["itemId"] ?? 0'), message: 'No access to ToolWithIsGrantedOnClass tool.')]
final class ToolWithIsGrantedOnClass
{
    public function __invoke(int $itemId): void
    {
    }
}
