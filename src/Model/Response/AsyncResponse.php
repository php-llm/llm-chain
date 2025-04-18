<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Response;

use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class AsyncResponse implements ResponseInterface
{
    private bool $isConverted = false;
    private ResponseInterface $convertedResponse;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly ResponseConverter $responseConverter,
        private readonly HttpResponse $response,
        private readonly array $options = [],
    ) {
    }

    public function getContent(): string|iterable|object|null
    {
        return $this->unwrap()->getContent();
    }

    public function unwrap(): ResponseInterface
    {
        if (!$this->isConverted) {
            $this->convertedResponse = $this->responseConverter->convert($this->response, $this->options);
            $this->isConverted = true;
        }

        return $this->convertedResponse;
    }

    /**
     * @param array<int, mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->unwrap()->{$name}(...$arguments);
    }

    public function __get(string $name): mixed
    {
        return $this->unwrap()->{$name};
    }
}
