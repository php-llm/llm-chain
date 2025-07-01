<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Google\Contract;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class GoogleSet
{
    /** @return array<NormalizerInterface> */
    public static function get(): array
    {
        return [
            new AssistantMessageNormalizer(),
            new MessageBagNormalizer(),
            new ToolNormalizer(),
            new ToolCallMessageNormalizer(),
            new UserMessageNormalizer(),
        ];
    }
}
