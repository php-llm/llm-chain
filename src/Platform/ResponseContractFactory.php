<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class ResponseContractFactory implements ResponseConverterInterface
{
    private ?Model $currentModel = null;

    public function __construct(
        private readonly ResponseContract $responseContract,
    ) {
    }

    public function supports(Model $model): bool
    {
        // Store the model temporarily for the next convert() call (cleared after use)
        $this->currentModel = $model;

        // Check if any of the registered denormalizers support this model
        return $this->responseContract->supportsModel($model);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        if (!$this->currentModel) {
            throw new \InvalidArgumentException('Model must be provided via supports() call first');
        }

        $model = $this->currentModel;
        $this->currentModel = null; // Clear state after use to prevent unexpected reuse

        return $this->responseContract->convertResponse(
            $model,
            $response,
            $options
        );
    }
}
