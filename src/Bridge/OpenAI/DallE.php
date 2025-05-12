<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI;

use PhpLlm\LlmChain\Model\Capability;
use PhpLlm\LlmChain\Model\Model;

class DallE extends Model
{
    public const DALL_E_2 = 'dall-e-2';
    public const DALL_E_3 = 'dall-e-3';

    /** @param array<string, mixed> $options The default options for the model usage */
    public function __construct(string $name = self::DALL_E_2, array $options = [])
    {
        $capabilities = [
            Capability::INPUT_TEXT,
            Capability::OUTPUT_IMAGE,
        ];

        parent::__construct($name, $capabilities, $options);
    }
}
