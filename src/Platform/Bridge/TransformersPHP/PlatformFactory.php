<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\TransformersPHP;

use Codewithkyrian\Transformers\Transformers;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final readonly class PlatformFactory
{
    public static function create(): Platform
    {
        if (!class_exists(Transformers::class)) {
            throw new RuntimeException('For using the TransformersPHP with FFI to run models in PHP, the codewithkyrian/transformers package is required. Try running "composer require codewithkyrian/transformers".');
        }

        return new Platform();
    }
}
