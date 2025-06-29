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

    public function __construct(
        ContentInterface ...$content,
    ) {
        $this->content = $content;
    }

    public function getRole(): Role
    {
        return Role::User;
    }

    public function getId(): Uuid
    {
        // Generate deterministic UUID based on content and role
        $contentData = serialize($this->content);
        $data = sprintf('user:%s', $contentData);

        return Uuid::v5(self::getNamespace(), $data);
    }

    private static function getNamespace(): Uuid
    {
        // Use a fixed namespace UUID for the LLM Chain message system
        // This ensures deterministic IDs across application runs
        return Uuid::fromString('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
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
