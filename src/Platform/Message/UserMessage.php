<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Message;

use PhpLlm\LlmChain\Platform\Message\Content\Audio;
use PhpLlm\LlmChain\Platform\Message\Content\ContentInterface;
use PhpLlm\LlmChain\Platform\Message\Content\Image;
use PhpLlm\LlmChain\Platform\Message\Content\ImageUrl;
use Symfony\Component\Uid\Uuid;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class UserMessage implements MessageInterface
{
    /**
     * @var list<ContentInterface>
     */
    public array $content;

    public Uuid $id;

    public function __construct(
        ContentInterface ...$content,
    ) {
        $this->content = $content;
        $this->id = Uuid::v7();
    }

    public function getRole(): Role
    {
        return Role::User;
    }

    public function getId(): Uuid
    {
        return $this->id;
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
