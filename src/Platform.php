<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use PhpLlm\LlmChain\Platform\ResponseFactory;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final readonly class Platform
{
    /**
     * @var ResponseFactory[]
     */
    private array $responseFactories;

    /**
     * @var ResponseConverter[]
     */
    private array $responseConverter;

    /**
     * @param iterable<ResponseFactory>   $responseFactories
     * @param iterable<ResponseConverter> $responseConverter
     */
    public function __construct(iterable $responseFactories, iterable $responseConverter)
    {
        $this->responseFactories = $responseFactories instanceof \Traversable ? iterator_to_array($responseFactories) : $responseFactories;
        $this->responseConverter = $responseConverter instanceof \Traversable ? iterator_to_array($responseConverter) : $responseConverter;
    }

    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    public function request(Model $model, array|string|object $input, array $options = []): ResponseInterface
    {
        $options = array_merge($model->getOptions(), $options);

        try {
            $response = $this->createResponse($model, $input, $options);

            return $this->convertResponse($model, $input, $response, $options);
        } catch (ClientExceptionInterface $e) {
            $message = $e->getMessage();

            throw new InvalidArgumentException('' === $message ? 'Invalid request to model or platform' : $message, 0, $e);
        } catch (HttpExceptionInterface $e) {
            throw new RuntimeException('Failed to request model', 0, $e);
        }
    }

    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    private function createResponse(Model $model, array|string|object $input, array $options = []): HttpResponse
    {
        foreach ($this->responseFactories as $responseFactory) {
            if ($responseFactory->supports($model, $input)) {
                return $responseFactory->create($model, $input, $options);
            }
        }

        throw new RuntimeException('No response factory found for the given model');
    }

    /**
     * @param array<mixed>|string|object $input
     * @param array<string, mixed>       $options
     */
    private function convertResponse(Model $model, object|array|string $input, HttpResponse $response, array $options): ResponseInterface
    {
        foreach ($this->responseConverter as $responseConverter) {
            if ($responseConverter->supports($model, $input)) {
                return new AsyncResponse($responseConverter, $response, $options);
            }
        }

        throw new RuntimeException('No response converter found for the given model');
    }
}
