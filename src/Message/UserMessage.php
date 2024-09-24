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
        ContentInterface ...$images,
    ) {
        $this->content = $images;
    }

    public function getRole(): Role
    {
        return Role::User;
    }

    /**
     * @return array{
     *     role: Role::User,
     *     content: string|list<array{type: 'text', text: string}|array{type: 'image_url', image_url: array{url: string}}>
     * }
     */
    public function jsonSerialize(): array
    {
        $array = ['role' => Role::User];
        if (1 === count($this->content) && $this->content[0] instanceof Text) {
            $array['content'] = $this->content[0]->text;

            return $array;
        }

        foreach ($this->content as $entry) {
            $array['content'][] = $entry->jsonSerialize();
        }

        return $array;
    }
}
