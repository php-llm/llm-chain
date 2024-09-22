<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Anthropic\Model;

use PhpLlm\LlmChain\Anthropic\Model\Claude\Version;
use Webmozart\Assert\Assert;

final class Model
{
    public function __construct(
        private string $name,
    ) {
        Assert::stringNotEmpty($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromVersion(Version $version): self
    {
        return new self($version->value);
    }
}
