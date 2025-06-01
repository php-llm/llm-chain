<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response\Exception;

use PhpLlm\LlmChain\Platform\Exception\RuntimeException;

final class RawResponseAlreadySetException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The raw response was already set.');
    }
}
