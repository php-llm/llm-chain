<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Response\AsyncResponse;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseInterface as HttpResponse;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class Platform implements PlatformInterface
{
    /**
     * @var ModelClientInterface[]
     */
    private readonly array $modelClients;

    /**
     * @var ResponseConverterInterface[]
     */
    private readonly array $responseConverter;

    /**
     * @param iterable<ModelClientInterface>       $modelClients
     * @param iterable<ResponseConverterInterface> $responseConverter
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

        if (isset($options['tools'])) {
            $options['tools'] = $this->contract->createToolOption($options['tools'], $model);
        }

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
