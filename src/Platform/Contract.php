<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Platform\Contract\PlatformSet;
use PhpLlm\LlmChain\Platform\Tool\Tool;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class Contract implements ContractInterface
{
    public const CONTEXT_MODEL = 'model';

    public function __construct(
        private NormalizerInterface $normalizer,
    ) {
    }

    public static function create(NormalizerInterface ...$normalizer): self
    {
        return new self(
            new Serializer(array_merge($normalizer, PlatformSet::get())),
        );
    }

    /**
     * @param object|array<string|int, mixed>|string $input
     *
     * @return array<string, mixed>|string
     */
    public function createRequestPayload(Model $model, object|array|string $input): string|array
    {
        return $this->normalizer->normalize($input, context: [self::CONTEXT_MODEL => $model]);
    }

    /**
     * @param Tool[] $tools
     *
     * @return array<string, mixed>
     */
    public function createToolOption(array $tools, Model $model): array
    {
        return $this->normalizer->normalize($tools, context: [self::CONTEXT_MODEL => $model, AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true]);
    }
}
