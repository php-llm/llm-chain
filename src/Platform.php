<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

final class Platform implements PlatformInterface
{
    /**
     * @var ModelClient[]
     */
    private readonly array $modelClients;

    /**
     * @var ResponseConverter[]
     */
    private readonly array $responseConverter;

    /**
     * @param iterable<ModelClient>       $modelClients
     * @param iterable<ResponseConverter> $responseConverter
     */
    public function __construct(
        iterable $modelClients,
        iterable $responseConverter,
        private ?Contract $contract = null,
    ) {
        $this->contract = $contract ?? Contract::create();
        $this->modelClients = $modelClients instanceof \Traversable ? iterator_to_array($modelClients) : $modelClients;
        $this->responseConverter = $responseConverter instanceof \Traversable ? iterator_to_array($responseConverter) : $responseConverter;
    }

    public function request(Model $model, array|string|object $input, array $options = []): ResponseInterface
    {
        $payload = $this->contract->createRequestPayload($model, $input);
        $options = array_merge($model->getOptions(), $options);

        $response = $this->doRequest($model, $payload, $options);

        return $this->convertResponse($model, $response, $options);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $options
     */
    private function doRequest(Model $model, array|string $payload, array $options = []): HttpResponse
    {
        foreach ($this->modelClients as $modelClient) {
            if ($modelClient->supports($model)) {
                return $modelClient->request($model, $payload, $options);
            }
        }

        throw new RuntimeException('No response factory registered for model "'.$model::class.'" with given input.');
    }

    /**
     * @param array<string, mixed> $options
     */
    private function convertResponse(Model $model, HttpResponse $response, array $options): ResponseInterface
    {
        foreach ($this->responseConverter as $responseConverter) {
            if ($responseConverter->supports($model)) {
                return new AsyncResponse($responseConverter, $response, $options);
            }
        }

        throw new RuntimeException('No response converter registered for model "'.$model::class.'" with given input.');
    }
}
