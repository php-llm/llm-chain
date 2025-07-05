<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform;

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
use PhpLlm\LlmChain\Platform\Tool\Tool;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
readonly class Contract
{
    public const CONTEXT_MODEL = 'model';

    final public function __construct(
        protected NormalizerInterface $normalizer,
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

        // Options
        $normalizer[] = new ToolNormalizer();

        // Response
        $normalizer[] = new ToolCallNormalizer();

        // JsonSerializable objects as extension point to library interfaces
        $normalizer[] = new JsonSerializableNormalizer();

        return new self(
            new Serializer($normalizer),
        );
    }

    /**
     * @param object|array<string|int, mixed>|string $input
     *
     * @return array<string, mixed>|string
     */
    final public function createRequestPayload(Model $model, object|array|string $input): string|array
    {
        return $this->normalizer->normalize($input, context: [self::CONTEXT_MODEL => $model]);
    }

    /**
     * @param Tool[] $tools
     *
     * @return array<string, mixed>
     */
    final public function createToolOption(array $tools, Model $model): array
    {
        return $this->normalizer->normalize($tools, context: [self::CONTEXT_MODEL => $model, AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => true]);
    }
}
