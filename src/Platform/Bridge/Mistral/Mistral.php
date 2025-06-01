<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Mistral;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class Mistral extends Model
{
    public const CODESTRAL = 'codestral-latest';
    public const CODESTRAL_MAMBA = 'open-codestral-mamba';
    public const MISTRAL_LARGE = 'mistral-large-latest';
    public const MISTRAL_SMALL = 'mistral-small-latest';
    public const MISTRAL_NEMO = 'open-mistral-nemo';
    public const MISTRAL_SABA = 'mistral-saba-latest';
    public const MINISTRAL_3B = 'mistral-3b-latest';
    public const MINISTRAL_8B = 'mistral-8b-latest';
    public const PIXSTRAL_LARGE = 'pixstral-large-latest';
    public const PIXSTRAL = 'pixstral-12b-latest';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        string $name = self::MISTRAL_LARGE,
        array $options = [],
    ) {
        $capabilities = [
            Capability::INPUT_MESSAGES,
            Capability::OUTPUT_TEXT,
            Capability::OUTPUT_STREAMING,
            Capability::OUTPUT_STRUCTURED,
        ];

        if (\in_array($name, [self::PIXSTRAL, self::PIXSTRAL_LARGE, self::MISTRAL_SMALL], true)) {
            $capabilities[] = Capability::INPUT_IMAGE;
        }

        if (\in_array($name, [
            self::CODESTRAL,
            self::MISTRAL_LARGE,
            self::MISTRAL_SMALL,
            self::MISTRAL_NEMO,
            self::MINISTRAL_3B,
            self::MINISTRAL_8B,
            self::PIXSTRAL,
            self::PIXSTRAL_LARGE,
        ], true)) {
            $capabilities[] = Capability::TOOL_CALLING;
        }

        parent::__construct($name, $capabilities, $options);
    }
}
