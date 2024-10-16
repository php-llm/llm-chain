<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class AsyncResponse implements ResponseInterface
{
    private bool $isConverted = false;
    private ResponseInterface $convertedResponse;

    public function __construct(
        private ResponseConverter $responseConverter,
        private HttpResponse $response,
        private array $options = [],
    ) {
    }

    public function getContent(): string|iterable|object|null
    {
        if (!$this->isConverted) {
            $this->convertedResponse = $this->responseConverter->convert($this->response, $this->options);
            $this->isConverted = true;
        }

        return $this->convertedResponse->getContent();
    }
}
