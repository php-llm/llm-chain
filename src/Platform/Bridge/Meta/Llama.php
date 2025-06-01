<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Meta;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
class Llama extends Model
{
    public const V3_3_70B_INSTRUCT = 'llama-3.3-70B-Instruct';
    public const V3_2_90B_VISION_INSTRUCT = 'llama-3.2-90b-vision-instruct';
    public const V3_2_11B_VISION_INSTRUCT = 'llama-3.2-11b-vision-instruct';
    public const V3_2_3B = 'llama-3.2-3b';
    public const V3_2_3B_INSTRUCT = 'llama-3.2-3b-instruct';
    public const V3_2_1B = 'llama-3.2-1b';
    public const V3_2_1B_INSTRUCT = 'llama-3.2-1b-instruct';
    public const V3_1_405B_INSTRUCT = 'llama-3.1-405b-instruct';
    public const V3_1_70B = 'llama-3.1-70b';
    public const V3_1_70B_INSTRUCT = 'llama-3-70b-instruct';
    public const V3_1_8B = 'llama-3.1-8b';
    public const V3_1_8B_INSTRUCT = 'llama-3.1-8b-instruct';
    public const V3_70B = 'llama-3-70b';
    public const V3_8B_INSTRUCT = 'llama-3-8b-instruct';
    public const V3_8B = 'llama-3-8b';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(string $name = self::V3_1_405B_INSTRUCT, array $options = [])
    {
        $capabilities = [
            Capability::INPUT_MESSAGES,
            Capability::OUTPUT_TEXT,
        ];

        parent::__construct($name, $capabilities, $options);
    }
}
