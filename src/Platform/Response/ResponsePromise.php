<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Response;

use PhpLlm\LlmChain\Platform\Exception\UnexpectedResponseTypeException;
use PhpLlm\LlmChain\Platform\Vector\Vector;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ResponsePromise
{
    private bool $isConverted = false;
    private ResponseInterface $convertedResponse;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private readonly \Closure $responseConverter,
        private readonly RawResponseInterface $response,
        private readonly array $options = [],
    ) {
    }

    public function getResponse(): ResponseInterface
    {
        return $this->await();
    }

    public function getRawResponse(): RawResponseInterface
    {
        return $this->response;
    }

    public function await(): ResponseInterface
    {
        if (!$this->isConverted) {
            $this->convertedResponse = ($this->responseConverter)($this->response->getRawObject(), $this->options);

            if (null === $this->convertedResponse->getRawResponse()) {
                // Fallback to set the raw response when it was not handled by the response converter itself
                $this->convertedResponse->setRawResponse($this->response);
            }

            $this->isConverted = true;
        }

        return $this->convertedResponse;
    }

    public function asText(): string
    {
        return $this->as(TextResponse::class)->getContent();
    }

    public function asObject(): object
    {
        return $this->as(ObjectResponse::class)->getContent();
    }

    public function asBinary(): string
    {
        return $this->as(BinaryResponse::class)->getContent();
    }

    public function asBase64(): string
    {
        $response = $this->as(BinaryResponse::class);

        \assert($response instanceof BinaryResponse);

        return $response->toDataUri();
    }

    /**
     * @return Vector[]
     */
    public function asVectors(): array
    {
        return $this->as(VectorResponse::class)->getContent();
    }

    public function asStream(): \Generator
    {
        yield from $this->as(StreamResponse::class)->getContent();
    }

    /**
     * @return ToolCall[]
     */
    public function asToolCalls(): array
    {
        return $this->as(ToolCallResponse::class)->getContent();
    }

    /**
     * @param class-string $type
     */
    private function as(string $type): ResponseInterface
    {
        $response = $this->getResponse();

        if (!$response instanceof $type) {
            throw new UnexpectedResponseTypeException($type, $response::class);
        }

        return $response;
    }
}
