<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Double;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingsModel;

final class SpyEmbeddingsModel implements EmbeddingsModel
{
    public function create(string $text, array $options = []): Vector
    {
        ++$this->callsCreate;

        return new Vector([1, 2, 3]);
    }

    public function multiCreate(array $texts, array $options = []): array
    {
        ++$this->callsMultiCreate;

        return [];
    }

    public int $callsCreate = 0;
    public int $callsMultiCreate = 0;
}
