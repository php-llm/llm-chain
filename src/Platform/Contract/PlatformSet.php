<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Contract;

use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\AssistantMessageNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content\AudioNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content\ImageNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content\ImageUrlNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\Content\TextNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\MessageBagNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\SystemMessageNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\ToolCallMessageNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\UserMessageNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Response\ToolCallNormalizer;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ToolNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class PlatformSet
{
    /** @return array<NormalizerInterface> */
    public static function get(): array
    {
        return [
            // Messages
            new MessageBagNormalizer(),
            new AssistantMessageNormalizer(),
            new SystemMessageNormalizer(),
            new ToolCallMessageNormalizer(),
            new UserMessageNormalizer(),

            // Message Content
            new AudioNormalizer(),
            new ImageNormalizer(),
            new ImageUrlNormalizer(),
            new TextNormalizer(),

            // Options
            new ToolNormalizer(),

            // Response
            new ToolCallNormalizer(),
        ];
    }
}
