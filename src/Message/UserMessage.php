<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;

final readonly class UserMessage implements MessageInterface
{
    /**
     * @var list<ContentInterface>
     */
    public array $content;

    public function __construct(
        ContentInterface ...$content,
    ) {
        $this->content = $content;
    }

    public function getRole(): Role
    {
        return Role::User;
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
     *     content: string|list<ContentInterface>
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
