<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\Exception\RawResponseAlreadySetException;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
trait RawResponseAwareTrait
{
    protected ?RawResponseInterface $rawResponse = null;

    public function setRawResponse(RawResponseInterface $rawResponse): void
    {
        if (isset($this->rawResponse)) {
            throw new RawResponseAlreadySetException();
        }

        $this->rawResponse = $rawResponse;
    }

    public function getRawResponse(): ?RawResponseInterface
    {
        return $this->rawResponse;
    }
}
