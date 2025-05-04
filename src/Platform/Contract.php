<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\Contract\Extension;
use PhpLlm\LlmChain\Platform\Contract\InputNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final readonly class Contract
{
    public const MODEL = 'model';

    public function __construct(
        private NormalizerInterface $normalizer,
    ) {
    }

    public function convertRequestPayload(object|array|string $input, ?Model $model = null): string|array
    {
        return $this->normalizer->normalize($input, null, [
            self::MODEL => $model,
        ]);
    }

    public static function create(Extension ...$extensions): self
    {
        return new self(
            new Serializer(
                [new InputNormalizer($extensions), new JsonSerializableNormalizer(), new ObjectNormalizer()],
                [new JsonEncoder()]
            )
        );
    }
}
