<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final readonly class Platform implements PlatformInterface
{
    /**
     * @var ModelClient[]
     */
    private array $modelClients;

    /**
     * @var ResponseConverter[]
     */
    private array $responseConverter;

    /**
     * @param iterable<ModelClient>       $modelClients
     * @param iterable<ResponseConverter> $responseConverter
     */
    public function __construct(iterable $modelClients, iterable $responseConverter)
    {
        $this->modelClients = $modelClients instanceof \Traversable ? iterator_to_array($modelClients) : $modelClients;
        $this->responseConverter = $responseConverter instanceof \Traversable ? iterator_to_array($responseConverter) : $responseConverter;
    }

    public function request(Model $model, array|string|object $input, array $options = []): ResponseInterface
    {
        $options = array_merge($model->getOptions(), $options);

        try {
            $response = $this->doRequest($model, $input, $options);

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
    private function doRequest(Model $model, array|string|object $input, array $options = []): HttpResponse
    {
        foreach ($this->modelClients as $modelClient) {
            if ($modelClient->supports($model, $input)) {
                return $modelClient->request($model, $input, $options);
            }
        }

        throw new RuntimeException('No response factory registered for model "'.$model::class.'" with given input.');
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

        throw new RuntimeException('No response converter registered for model "'.$model::class.'" with given input.');
    }
}
