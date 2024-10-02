<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Double;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingsModel;
use Webmozart\Assert\Assert;

final class TestEmbeddingsModel implements EmbeddingsModel
{
    public int $createCalls = 0;
    public int $multiCreateCalls = 0;

    public function __construct(
        private readonly ?Vector $create = null,
        private readonly array $multiCreate = [])
    {
        Assert::allIsInstanceOf($multiCreate, Vector::class);
    }

    public function create(string $text, array $options = []): Vector
    {
        ++$this->createCalls;

        return $this->create ?? new Vector([1, 2, 3]);
    }

    public function multiCreate(array $texts, array $options = []): array
    {
        ++$this->multiCreateCalls;

        return $this->multiCreate;
    }
}
