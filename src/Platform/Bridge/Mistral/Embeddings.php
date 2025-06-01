<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Mistral;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;

final class Embeddings extends Model
{
    public const MISTRAL_EMBED = 'mistral-embed';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        string $name = self::MISTRAL_EMBED,
        array $options = [],
    ) {
        parent::__construct($name, [Capability::INPUT_MULTIPLE], $options);
    }
}
