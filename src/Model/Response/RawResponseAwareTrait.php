<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use PhpLlm\LlmChain\Model\Response\Exception\RawResponseAlreadySet;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

trait RawResponseAwareTrait
{
    protected ?SymfonyHttpResponse $rawResponse = null;

    public function setRawResponse(SymfonyHttpResponse $rawResponse): void
    {
        if (null !== $this->rawResponse) {
            throw new RawResponseAlreadySet();
        }

        $this->rawResponse = $rawResponse;
    }

    public function getRawResponse(): ?SymfonyHttpResponse
    {
        return $this->rawResponse;
    }
}
