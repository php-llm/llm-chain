<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Message\Content\Content;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;

final readonly class UserMessage implements Message
{
    use HasMetadata;
    use HasRole;

    /**
     * @var Content[]
     */
    public array $content;

    public function __construct(
        string|Content ...$content,
    ) {
        $this->metadata = new Metadata();
        $this->role = Role::User;

        $content = \array_map(
            static fn (string|Content $entry) => \is_string($entry) ? new Text($entry) : $entry,
            $content,
        );
        $this->content = $content;
    }

    public function hasImageContent(): bool
    {
        foreach ($this->content as $content) {
            if ($content instanceof Image) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{
     *     role: Role::User,
     *     content: string|Content[]
     * }
     */
    public function jsonSerialize(): array
    {
        $array = ['role' => Role::User];
        if (1 === count($this->content) && $this->content[0] instanceof Text) {
            $array['content'] = $this->content[0]->text;

            return $array;
        }

        $array['content'] = $this->content;

        return $array;
    }
}
