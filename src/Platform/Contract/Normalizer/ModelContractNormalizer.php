<?php

namespace PhpLlm\LlmChain\Platform\Contract\Normalizer;

use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
abstract class ModelContractNormalizer implements NormalizerInterface
{
    /**
     * @return class-string
     */
    abstract protected function supportedDataClass(): string;

    abstract protected function supportsModel(Model $model): bool;

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (!is_a($data, $this->supportedDataClass(), true)) {
            return false;
        }

        if (isset($context[Contract::CONTEXT_MODEL]) && $context[Contract::CONTEXT_MODEL] instanceof Model) {
            return $this->supportsModel($context[Contract::CONTEXT_MODEL]);
        }

        return false;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            $this->supportedDataClass() => true,
        ];
    }
}
