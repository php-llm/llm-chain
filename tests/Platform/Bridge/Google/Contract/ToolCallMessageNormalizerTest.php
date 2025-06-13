<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Google\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Google\Contract\ToolCallMessageNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversClass(ToolCallMessageNormalizer::class)]
#[UsesClass(Model::class)]
#[UsesClass(Gemini::class)]
#[UsesClass(ToolCallMessage::class)]
#[UsesClass(ToolCall::class)]
final class ToolCallMessageNormalizerTest extends TestCase
{
    #[Test]
    public function supportsNormalization(): void
    {
        $normalizer = new ToolCallMessageNormalizer();

        self::assertTrue($normalizer->supportsNormalization(new ToolCallMessage(new ToolCall('', '', []), ''), context: [
            Contract::CONTEXT_MODEL => new Gemini(),
        ]));
        self::assertFalse($normalizer->supportsNormalization('not a tool call'));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        $normalizer = new ToolCallMessageNormalizer();

        $expected = [
            ToolCallMessage::class => true,
        ];

        self::assertSame($expected, $normalizer->getSupportedTypes(null));
    }

    #[Test]
    #[DataProvider('normalizeDataProvider')]
    public function normalize(ToolCallMessage $message, array $expected): void
    {
        $normalizer = new ToolCallMessageNormalizer();

        $normalized = $normalizer->normalize($message);

        self::assertEquals($expected, $normalized);
    }

    /**
     * @return iterable<array{0: ToolCallMessage, 1: array}>
     */
    public static function normalizeDataProvider(): iterable
    {
        yield 'scalar' => [
            new ToolCallMessage(
                new ToolCall('id1', 'name1', ['foo' => 'bar']),
                'true',
            ),
            [[
                'functionResponse' => [
                    'id' => 'id1',
                    'name' => 'name1',
                    'response' => ['rawResponse' => 'true'],
                ],
            ]],
        ];

        yield 'structured response' => [
            new ToolCallMessage(
                new ToolCall('id1', 'name1', ['foo' => 'bar']),
                '{"structured":"response"}',
            ),
            [[
                'functionResponse' => [
                    'id' => 'id1',
                    'name' => 'name1',
                    'response' => ['structured' => 'response'],
                ],
            ]],
        ];
    }
}
