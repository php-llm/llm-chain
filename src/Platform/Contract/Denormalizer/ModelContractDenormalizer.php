<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract\Denormalizer;

use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\ResponseContract;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

abstract class ModelContractDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    abstract protected function supportsModel(Model $model): bool;

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (isset($context[ResponseContract::CONTEXT_MODEL]) && $context[ResponseContract::CONTEXT_MODEL] instanceof Model) {
            return $this->supportsModel($context[ResponseContract::CONTEXT_MODEL]);
        }

        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }
}
