<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Voyage\Model\Voyage;

use Webmozart\Assert\Assert;

final readonly class Version
{
    public function __construct(
        public string $name,
    ) {
        Assert::stringNotEmpty($name);
    }

    public static function v3(): self
    {
        return new self('voyage-3');
    }

    public static function v3lite(): self
    {
        return new self('voyage-3-lite');
    }

    public static function finance2(): self
    {
        return new self('voyage-finance-2');
    }

    public static function multilingual2(): self
    {
        return new self('voyage-multilingual-2');
    }

    public static function law2(): self
    {
        return new self('voyage-law-2');
    }

    public static function code2(): self
    {
        return new self('voyage-code-2');
    }
}
