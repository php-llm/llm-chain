<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\Exception\RawResponseAlreadySetException;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
trait RawResponseAwareTrait
{
    protected ?SymfonyHttpResponse $rawResponse = null;

    public function setRawResponse(SymfonyHttpResponse $rawResponse): void
    {
        if (null !== $this->rawResponse) {
            throw new RawResponseAlreadySetException();
        }

        $this->rawResponse = $rawResponse;
    }

    public function getRawResponse(): ?SymfonyHttpResponse
    {
        return $this->rawResponse;
    }

    public function __serialize(): array
    {
        $data = get_object_vars($this);
        unset($data['rawResponse']);

        return $data;
    }
}
