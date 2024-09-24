<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;

final readonly class UserMessage implements MessageInterface
{
    /**
     * @var list<Image>
     */
    public array $images;

    public function __construct(
        public Text $text,
        Image ...$images,
    ) {
        $this->images = $images;
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
        if ([] === $this->images) {
            $array['content'] = $this->text->text;

            return $array;
        }

        $array['content'][] = $this->text->jsonSerialize();

        foreach ($this->images as $image) {
            $array['content'][] = $image->jsonSerialize();
        }

        return $array;
    }
}
