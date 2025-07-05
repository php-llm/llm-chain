<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Google\Contract;

use PhpLlm\LlmChain\Platform\Contract;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class GoogleContract extends Contract
{
    public static function create(NormalizerInterface ...$normalizer): Contract
    {
        return parent::create(
            new AssistantMessageNormalizer(),
            new MessageBagNormalizer(),
            new ToolNormalizer(),
            new ToolCallMessageNormalizer(),
            new UserMessageNormalizer(),
            ...$normalizer,
        );
    }
}
