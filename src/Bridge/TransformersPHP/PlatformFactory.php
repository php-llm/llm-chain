<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\TransformersPHP;

use Codewithkyrian\Transformers\Transformers;
use PhpLlm\LlmChain\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform;

final readonly class PlatformFactory
{
    public static function create(): Platform
    {
        if (!class_exists(Transformers::class)) {
            throw new RuntimeException('TransformersPHP is not installed. Please install it using "composer require codewithkyrian/transformers".');
        }

        return new Platform([$handler = new Handler()], [$handler]);
    }
}
