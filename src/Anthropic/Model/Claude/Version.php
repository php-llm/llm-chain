<?php

declare(strict_types=1);

/*
 * This file is part of php-llm/llm-chain.
 *
 * (c) Christopher Hertel <mail@christopher-hertel.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpLlm\LlmChain\Anthropic\Model\Claude;

use Webmozart\Assert\Assert;

final readonly class Version
{
    public function __construct(
        public string $name,
    ) {
        Assert::stringNotEmpty($name);
    }

    public static function haiku3(): self
    {
        return new self('claude-3-haiku-20240307');
    }

    public static function sonnet3(): self
    {
        return new self('claude-3-sonnet-20240229');
    }

    public static function sonnet35(): self
    {
        return new self('claude-3-5-sonnet-20240620');
    }

    public static function opus(): self
    {
        return new self('claude-3-opus-20240229');
    }
}
