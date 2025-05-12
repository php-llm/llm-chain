<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace;

interface Provider
{
    public const CEREBRAS = 'cerebras';
    public const COHERE = 'cohere';
    public const FAL_AI = 'fal-ai';
    public const FIREWORKS = 'fireworks-ai';
    public const HYPERBOLIC = 'hyperbolic';
    public const HF_INFERENCE = 'hf-inference';
    public const NEBIUS = 'nebius';
    public const NOVITA = 'novita';
    public const REPLICATE = 'replicate';
    public const SAMBA_NOVA = 'sambanova';
    public const TOGETHER = 'together';
}
