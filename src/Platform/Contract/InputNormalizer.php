<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract;

use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\Contract;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class InputNormalizer implements NormalizerInterface
{
    /**
     * @var Extension[]
     */
    private array $extensions = [];

    public function __construct(iterable $extensions)
    {
        $this->extensions = $extensions instanceof \Traversable ? iterator_to_array($extensions) : $extensions;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (!isset($context[Contract::MODEL]) || !$context[Contract::MODEL] instanceof Model) {
            return false;
        }

        try {
            $this->getHandler($context[Contract::MODEL], $data);
        } catch (RuntimeException) {
            return false;
        }

        return true;
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return ($this->getHandler($context[Contract::MODEL], $data))($data);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            '*' => true,
        ];
    }

    private function getHandler(Model $model, mixed $data): \Closure
    {
        foreach ($this->extensions as $extension) {
            if ($extension->supports($model)) {
                $types = $extension->registerTypes();
                foreach ($types as $type => $handler) {
                    if (is_subclass_of($data, $type)
                    || (is_string($data) && 'string' === $type)
                    || (is_int($data) && 'int' === $type)
                    || (is_float($data) && 'float' === $type)
                    || $data instanceof $type) {
                        return $extension->{$handler}(...);
                    }
                }
            }
        }

        throw new RuntimeException('No handler found for the data.');
    }
}
