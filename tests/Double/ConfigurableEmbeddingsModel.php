<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Double;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingsModel;
use Webmozart\Assert\Assert;

final readonly class ConfigurableEmbeddingsModel implements EmbeddingsModel
{
    public function __construct(
        private ?Vector $create = null,
        private array $multiCreate = [])
    {
        Assert::allIsInstanceOf($multiCreate, Vector::class);
    }

    public function create(string $text, array $options = []): Vector
    {
        if (null === $this->create) {
            throw new \RuntimeException('No vector configured');
        }

        return $this->create;
    }

    public function multiCreate(array $texts, array $options = []): array
    {
        return $this->multiCreate;
    }
}
