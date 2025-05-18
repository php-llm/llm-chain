<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

use PhpLlm\LlmChain\Model\Model;
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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

final readonly class Contract
{
    public const CONTEXT_MODEL = 'model';

    public function __construct(
        private NormalizerInterface $normalizer,
    ) {
    }

    public static function create(NormalizerInterface ...$normalizer): self
    {
        // Messages
        $normalizer[] = new MessageBagNormalizer();
        $normalizer[] = new AssistantMessageNormalizer();
        $normalizer[] = new SystemMessageNormalizer();
        $normalizer[] = new ToolCallMessageNormalizer();
        $normalizer[] = new UserMessageNormalizer();

        // Message Content
        $normalizer[] = new AudioNormalizer();
        $normalizer[] = new ImageNormalizer();
        $normalizer[] = new ImageUrlNormalizer();
        $normalizer[] = new TextNormalizer();

        // Response
        $normalizer[] = new ToolCallNormalizer();

        return new self(
            new Serializer($normalizer),
        );
    }

    /**
     * @param object|array<string|int, mixed>|string $input
     *
     * @return array<string, mixed>|string
     */
    public function createRequestPayload(Model $model, object|array|string $input): string|array
    {
        return $this->normalizer->normalize($input, context: [self::CONTEXT_MODEL => $model]);
    }
}
