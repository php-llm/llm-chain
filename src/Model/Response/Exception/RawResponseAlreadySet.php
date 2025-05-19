<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response\Exception;

final class RawResponseAlreadySet extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('The raw response was already set.');
    }
}
