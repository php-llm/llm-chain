<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Google\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\AssistantMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Message\AssistantMessage;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversClass(AssistantMessageNormalizer::class)]
#[UsesClass(Gemini::class)]
#[UsesClass(AssistantMessage::class)]
#[UsesClass(Model::class)]
#[UsesClass(ToolCall::class)]
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

    #[DataProvider('normalizeDataProvider')]
    #[Test]
    public function normalize(AssistantMessage $message, array $expectedOutput): void
    {
        $normalizer = new AssistantMessageNormalizer();

        $normalized = $normalizer->normalize($message);

        self::assertSame($expectedOutput, $normalized);
    }

    /**
     * @return iterable<string, array{AssistantMessage, array{text?: string, functionCall?: array{id: string, name: string, args?: mixed}}[]}>
     */
    public static function normalizeDataProvider(): iterable
    {
        yield 'assistant message' => [
            new AssistantMessage('Great to meet you. What would you like to know?'),
            [['text' => 'Great to meet you. What would you like to know?']],
        ];
        yield 'function call' => [
            new AssistantMessage(toolCalls: [new ToolCall('id1', 'name1', ['arg1' => '123'])]),
            [['functionCall' => ['id' => 'id1', 'name' => 'name1', 'args' => ['arg1' => '123']]]],
        ];
        yield 'function call without parameters' => [
            new AssistantMessage(toolCalls: [new ToolCall('id1', 'name1')]),
            [['functionCall' => ['id' => 'id1', 'name' => 'name1']]],
        ];
    }
}
