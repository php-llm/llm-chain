<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Message;

use PhpLlm\LlmChain\Model\Message\Content\Audio;
use PhpLlm\LlmChain\Model\Message\Content\Content;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\ImageUrl;

final readonly class UserMessage implements MessageInterface
{
    /**
     * @var list<Content>
     */
    public array $content;

    public function __construct(
        Content ...$content,
    ) {
        $this->content = $content;
    }

    public function getRole(): Role
    {
        return Role::User;
    }

    public function hasAudioContent(): bool
    {
        foreach ($this->content as $content) {
            if ($content instanceof Audio) {
                return true;
            }
        }

        return false;
    }

    public function hasImageContent(): bool
    {
        foreach ($this->content as $content) {
            if ($content instanceof Image || $content instanceof ImageUrl) {
                return true;
            }
        }

        return false;
    }
}
