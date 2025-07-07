<?php

namespace PhpLlm\LlmChain\Tests\Fixture\Tool;

use PhpLlm\LlmChain\Chain\Toolbox\Security\Attribute\IsGrantedTool;
use Symfony\Component\ExpressionLanguage\Expression;

final class ToolWithIsGrantedOnMethod
{
    #[IsGrantedTool('ROLE_USER', message: 'No access to simple tool.')]
    public function simple(): bool
    {
        return true;
    }

    #[IsGrantedTool('test:permission', 'itemId', message: 'No access to argumentAsSubject tool.')]
    public function argumentAsSubject(int $itemId): int
    {
        return $itemId;
    }

    #[IsGrantedTool('test:permission', new Expression('args["itemId"]'), message: 'No access to expressionAsSubject tool.')]
    public function expressionAsSubject(int $itemId): int
    {
        return $itemId;
    }
}
