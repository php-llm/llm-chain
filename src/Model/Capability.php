<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model;

class Capability
{
    // INPUT
    public const INPUT_AUDIO = 'input-audio';
    public const INPUT_IMAGE = 'input-image';
    public const INPUT_MESSAGES = 'input-messages';
    public const INPUT_MULTIPLE = 'input-multiple';
    public const INPUT_PDF = 'input-pdf';
    public const INPUT_TEXT = 'input-text';

    // OUTPUT
    public const OUTPUT_AUDIO = 'output-audio';
    public const OUTPUT_IMAGE = 'output-image';
    public const OUTPUT_STREAMING = 'output-streaming';
    public const OUTPUT_STRUCTURED = 'output-structured';
    public const OUTPUT_TEXT = 'output-text';

    // FUNCTIONALITY
    public const TOOL_CALLING = 'tool-calling';
}
