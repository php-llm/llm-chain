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

namespace PhpLlm\LlmChain\OpenAI\Model\Gpt;

use Webmozart\Assert\Assert;

final readonly class Version
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public string $name,
        public bool $supportStructuredOutput,
    ) {
        Assert::stringNotEmpty($name);
    }

    public static function gpt35Turbo(): self
    {
        return new self('gpt-3.5-turbo', false);
    }

    public static function gpt35TurboInstruct(): self
    {
        return new self('gpt-3.5-turbo-instruct', false);
    }

    public static function gpt4(): self
    {
        return new self('gpt-4', false);
    }

    public static function gpt4Turbo(): self
    {
        return new self('gpt-4-turbo', false);
    }

    public static function gpt4o(): self
    {
        return new self('gpt-4o', true);
    }

    public static function gpt4oMini(): self
    {
        return new self('gpt-4o-mini', true);
    }

    public static function o1Mini(): self
    {
        return new self('o1-mini', false);
    }

    public static function o1Preview(): self
    {
        return new self('o1-preview', false);
    }
}
