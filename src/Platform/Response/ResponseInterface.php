<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\Exception\RawResponseAlreadySetException;
use PhpLlm\LlmChain\Platform\Response\Metadata\Metadata;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
interface ResponseInterface
{
    /**
     * @return string|iterable<mixed>|object|null
     */
    public function getContent(): string|iterable|object|null;

    public function getMetadata(): Metadata;

    public function getRawResponse(): ?SymfonyHttpResponse;

    /**
     * @throws RawResponseAlreadySetException if the response is tried to be set more than once
     */
    public function setRawResponse(SymfonyHttpResponse $rawResponse): void;
}
