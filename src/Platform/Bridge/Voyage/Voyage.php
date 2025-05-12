<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Voyage;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;

class Voyage extends Model
{
    public const V3 = 'voyage-3';
    public const V3_LITE = 'voyage-3-lite';
    public const FINANCE_2 = 'voyage-finance-2';
    public const MULTILINGUAL_2 = 'voyage-multilingual-2';
    public const LAW_2 = 'voyage-law-2';
    public const CODE_2 = 'voyage-code-2';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(string $name = self::V3, array $options = [])
    {
        parent::__construct($name, [Capability::INPUT_MULTIPLE], $options);
    }
}
