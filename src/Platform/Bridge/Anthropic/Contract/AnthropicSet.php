<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class AnthropicSet
{
    /** @return array<NormalizerInterface> */
    public static function get(): array
    {
        return [
            new AssistantMessageNormalizer(),
            new DocumentNormalizer(),
            new DocumentUrlNormalizer(),
            new ImageNormalizer(),
            new ImageUrlNormalizer(),
            new MessageBagNormalizer(),
            new ToolCallMessageNormalizer(),
            new ToolNormalizer(),
        ];
    }
}
