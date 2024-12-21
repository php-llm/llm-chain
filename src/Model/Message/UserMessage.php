<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

use PhpLlm\LlmChain\Model\Message\Content\Content;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;

final readonly class UserMessage extends Message
{
    /**
     * @var list<Content>
     */
    public array $content;

    public function __construct(
        Content ...$content,
    ) {
        $this->content = $content;

        parent::__construct(Role::User);
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
     *     content: string|list<Content>
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
