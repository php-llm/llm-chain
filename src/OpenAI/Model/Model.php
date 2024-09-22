<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\OpenAI\Model;

use PhpLlm\LlmChain\OpenAI\Model\Embeddings\Version as EmbeddingsVersion;
use PhpLlm\LlmChain\OpenAI\Model\Gpt\Version as GptVersion;
use Webmozart\Assert\Assert;

final class Model
{
    /**
     * @param non-empty-string $name
     */
    public function __construct(
        private string $name,
        private bool $supportStructuredOutput,
    ) {
        Assert::stringNotEmpty($name);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function supportsStructuredOutput(): bool
    {
        return $this->supportStructuredOutput;
    }

    public static function fromVersion(GptVersion|EmbeddingsVersion $version): self
    {
        if ($version instanceof EmbeddingsVersion) {
            return new self($version->value, false);
        }

        return new self($version->value, $version->supportsStructuredOutput());
    }
}
