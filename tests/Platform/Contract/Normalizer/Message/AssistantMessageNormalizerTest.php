<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\AssistantMessageNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[CoversClass(AssistantMessageNormalizer::class)]
final class AssistantMessageNormalizerTest extends TestCase
{
    private AssistantMessageNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new AssistantMessageNormalizer();
    }

    #[Test]
    public function supportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new AssistantMessage('content')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        self::assertSame([AssistantMessage::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalizeWithContent(): void
    {
        $message = new AssistantMessage('I am an assistant');

        $expected = [
            'role' => 'assistant',
            'content' => 'I am an assistant',
        ];

        self::assertSame($expected, $this->normalizer->normalize($message));
    }

    #[Test]
    public function normalizeWithToolCalls(): void
    {
        $toolCalls = [
            new ToolCall('id1', 'function1', ['param' => 'value']),
            new ToolCall('id2', 'function2', ['param' => 'value2']),
        ];
        $message = new AssistantMessage('Content with tools', $toolCalls);

        $expectedToolCalls = [
            ['id' => 'id1', 'function' => 'function1', 'arguments' => ['param' => 'value']],
            ['id' => 'id2', 'function' => 'function2', 'arguments' => ['param' => 'value2']],
        ];

        $innerNormalizer = $this->createMock(NormalizerInterface::class);
        $innerNormalizer->expects(self::once())
            ->method('normalize')
            ->with($message->toolCalls, null, [])
            ->willReturn($expectedToolCalls);

        $this->normalizer->setNormalizer($innerNormalizer);

        $expected = [
            'role' => 'assistant',
            'content' => 'Content with tools',
            'tool_calls' => $expectedToolCalls,
        ];

        self::assertSame($expected, $this->normalizer->normalize($message));
    }

    #[Test]
    public function normalizeWithNullContent(): void
    {
        $toolCalls = [new ToolCall('id1', 'function1', ['param' => 'value'])];
        $message = new AssistantMessage(null, $toolCalls);

        $expectedToolCalls = [['id' => 'id1', 'function' => 'function1', 'arguments' => ['param' => 'value']]];

        $innerNormalizer = $this->createMock(NormalizerInterface::class);
        $innerNormalizer->expects(self::once())
            ->method('normalize')
            ->with($message->toolCalls, null, [])
            ->willReturn($expectedToolCalls);

        $this->normalizer->setNormalizer($innerNormalizer);

        $expected = [
            'role' => 'assistant',
            'tool_calls' => $expectedToolCalls,
        ];

        self::assertSame($expected, $this->normalizer->normalize($message));
    }
}
