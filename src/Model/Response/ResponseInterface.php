<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use PhpLlm\LlmChain\Model\Response\Exception\RawResponseAlreadySet;
use PhpLlm\LlmChain\Model\Response\Metadata\Metadata;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

interface ResponseInterface
{
    /**
     * @return string|iterable<mixed>|object|null
     */
    public function getContent(): string|iterable|object|null;

    public function getMetadata(): Metadata;

    public function getRawResponse(): ?SymfonyHttpResponse;

    /**
     * @throws RawResponseAlreadySet if the response is tried to be set more than once
     */
    public function setRawResponse(SymfonyHttpResponse $rawResponse): void;
}
