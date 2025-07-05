<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de
 */
final readonly class RawHttpResponse implements RawResponseInterface
{
    public function __construct(
        private ResponseInterface $response,
    ) {
    }

    public function getRawData(): array
    {
        return $this->response->toArray(false);
    }

    public function getRawObject(): ResponseInterface
    {
        return $this->response;
    }
}
