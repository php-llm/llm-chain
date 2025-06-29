<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\Content\Audio;
use PhpLlm\LlmChain\Platform\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Platform\Message\Content\Image;
use PhpLlm\LlmChain\Platform\Message\Content\ImageUrl;
use Symfony\Component\Uid\UuidV7;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class UserMessage implements MessageInterface
{
    /**
     * @var list<ContentInterface>
     */
    public array $content;

    public UuidV7 $uid;

    public function __construct(
        ContentInterface ...$content,
    ) {
        $this->content = $content;
        $this->uid = new UuidV7();
    }

    public function getRole(): Role
    {
        return Role::User;
    }

    public function getUid(): UuidV7
    {
        return $this->uid;
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
