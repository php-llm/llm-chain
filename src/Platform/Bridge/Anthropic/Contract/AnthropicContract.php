<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Anthropic\Contract;

use PhpLlm\LlmChain\Platform\Contract;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class AnthropicContract extends Contract
{
    public static function create(NormalizerInterface ...$normalizer): Contract
    {
        return parent::create(
            new AssistantMessageNormalizer(),
            new DocumentNormalizer(),
            new DocumentUrlNormalizer(),
            new ImageNormalizer(),
            new ImageUrlNormalizer(),
            new MessageBagNormalizer(),
            new ToolCallMessageNormalizer(),
            new ToolNormalizer(),
            ...$normalizer,
        );
    }
}
