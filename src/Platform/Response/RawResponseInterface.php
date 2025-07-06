<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

/**
 * Base class for raw model responses.
 *
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface RawResponseInterface
{
    /**
     * Returns an array representation of the raw response data.
     *
     * @return array<string, mixed>
     */
    public function getRawData(): array;

    public function getRawObject(): object;
}
