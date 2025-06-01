<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\Toolbox\Exception\ToolException;
use PhpLlm\LlmChain\Platform\Tool\Tool;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface ToolFactoryInterface
{
    /**
     * @return iterable<Tool>
     *
     * @throws ToolException if the metadata for the given reference is not found
     */
    public function getTool(string $reference): iterable;
}
