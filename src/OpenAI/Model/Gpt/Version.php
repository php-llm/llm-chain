<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model\Gpt;

use Webmozart\Assert\Assert;

final readonly class Version
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        public string $name,
        public bool $supportImageInput = false,
        public bool $supportStructuredOutput = false,
    ) {
        Assert::stringNotEmpty($name);
    }

    public static function gpt35Turbo(): self
    {
        return new self('gpt-3.5-turbo');
    }

    public static function gpt35TurboInstruct(): self
    {
        return new self('gpt-3.5-turbo-instruct');
    }

    public static function gpt4(): self
    {
        return new self('gpt-4');
    }

    public static function gpt4Turbo(): self
    {
        return new self('gpt-4-turbo', true);
    }

    public static function gpt4o(): self
    {
        return new self('gpt-4o', true, true);
    }

    public static function gpt4oMini(): self
    {
        return new self('gpt-4o-mini', true, true);
    }

    public static function o1Mini(): self
    {
        return new self('o1-mini', true, false);
    }

    public static function o1Preview(): self
    {
        return new self('o1-preview', true, false);
    }
}
