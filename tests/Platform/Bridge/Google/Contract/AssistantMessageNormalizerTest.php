<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Google\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\AssistantMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversClass(AssistantMessageNormalizer::class)]
#[UsesClass(Gemini::class)]
#[UsesClass(AssistantMessage::class)]
#[UsesClass(Model::class)]
final class AssistantMessageNormalizerTest extends TestCase
{
    #[Test]
    public function supportsNormalization(): void
    {
        $normalizer = new AssistantMessageNormalizer();

        self::assertTrue($normalizer->supportsNormalization(new AssistantMessage('Hello'), context: [
            Contract::CONTEXT_MODEL => new Gemini(),
        ]));
        self::assertFalse($normalizer->supportsNormalization('not an assistant message'));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        $normalizer = new AssistantMessageNormalizer();

        self::assertSame([AssistantMessage::class => true], $normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalize(): void
    {
        $normalizer = new AssistantMessageNormalizer();
        $message = new AssistantMessage('Great to meet you. What would you like to know?');

        $normalized = $normalizer->normalize($message);

        self::assertSame([['text' => 'Great to meet you. What would you like to know?']], $normalized);
    }
}
