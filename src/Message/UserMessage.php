<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Message;

use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Content\Text;

final readonly class UserMessage implements MessageInterface
{
    /**
     * @var list<Image|string>
     */
    public array $images;

    public function __construct(
        public Text|string $content,
        Image|string ...$images,
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
            $array['content'] = \is_string($this->content) ? $this->content : $this->content->text;

            return $array;
        }

        $content = \is_string($this->content) ? new Text($this->content) : $this->content;

        $array['content'][] = $content->jsonSerialize();

        foreach ($this->images as $image) {
            $image = \is_string($image) ? new Image($image) : $image;

            $array['content'][] = $image->jsonSerialize();
        }

        return $array;
    }
}
