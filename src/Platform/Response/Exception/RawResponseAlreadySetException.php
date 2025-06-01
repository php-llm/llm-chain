<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response\Exception;

use PhpLlm\LlmChain\Platform\Exception\RuntimeException;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final class RawResponseAlreadySetException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('The raw response was already set.');
    }
}
