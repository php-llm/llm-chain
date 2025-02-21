<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message\Content;

interface Content extends \JsonSerializable
{
    public function accept(ContentVisitor $visitor): array;
}
