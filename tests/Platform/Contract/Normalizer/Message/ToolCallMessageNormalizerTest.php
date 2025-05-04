<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Model\Message\ToolCallMessage;
use PhpLlm\LlmChain\Model\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\ToolCallMessageNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[CoversClass(ToolCallMessageNormalizer::class)]
final class ToolCallMessageNormalizerTest extends TestCase
{
    private ToolCallMessageNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ToolCallMessageNormalizer();
    }

    #[Test]
    public function supportsNormalization(): void
    {
        $toolCallMessage = new ToolCallMessage(new ToolCall('id', 'function'), 'content');

        self::assertTrue($this->normalizer->supportsNormalization($toolCallMessage));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        self::assertSame([ToolCallMessage::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalize(): void
    {
        $toolCall = new ToolCall('tool_call_123', 'get_weather', ['location' => 'Paris']);
        $message = new ToolCallMessage($toolCall, 'Weather data for Paris');
        $expectedContent = 'Normalized weather data for Paris';

        $innerNormalizer = $this->createMock(NormalizerInterface::class);
        $innerNormalizer->expects(self::once())
            ->method('normalize')
            ->with($message->content, null, [])
            ->willReturn($expectedContent);

        $this->normalizer->setNormalizer($innerNormalizer);

        $expected = [
            'role' => 'tool',
            'content' => $expectedContent,
            'tool_call_id' => 'tool_call_123',
        ];

        self::assertSame($expected, $this->normalizer->normalize($message));
    }
}
