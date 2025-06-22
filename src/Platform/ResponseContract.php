<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class ResponseContract
{
    public const CONTEXT_MODEL = 'llm_model';
    public const CONTEXT_OPTIONS = 'llm_options';
    public const CONTEXT_HTTP_RESPONSE = 'http_response';

    public function __construct(
        private DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function convertResponse(
        Model $model,
        ResponseInterface $response,
        array $options = [],
    ): LlmResponse {
        $context = [
            self::CONTEXT_MODEL => $model,
            self::CONTEXT_OPTIONS => $options,
            self::CONTEXT_HTTP_RESPONSE => $response,
        ];

        return $this->denormalizer->denormalize(
            $response->toArray(false),
            LlmResponse::class,
            null,
            $context
        );
    }

    public function asConverter(): ResponseConverterInterface
    {
        return new ResponseContractFactory($this);
    }

    public function supportsModel(Model $model): bool
    {
        // Check if any denormalizer supports this model by testing denormalization
        $context = [self::CONTEXT_MODEL => $model];

        return $this->denormalizer->supportsDenormalization([], LlmResponse::class, null, $context);
    }
}
