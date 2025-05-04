<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Model\Message\Content\ImageUrl;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\UserMessageNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[CoversClass(UserMessageNormalizer::class)]
final class UserMessageNormalizerTest extends TestCase
{
    private UserMessageNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new UserMessageNormalizer();
    }

    #[Test]
    public function supportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new UserMessage(new Text('content'))));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        self::assertSame([UserMessage::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalizeWithSingleTextContent(): void
    {
        $textContent = new Text('Hello, how can you help me?');
        $message = new UserMessage($textContent);

        $expected = [
            'role' => 'user',
            'content' => 'Hello, how can you help me?',
        ];

        self::assertSame($expected, $this->normalizer->normalize($message));
    }

    #[Test]
    public function normalizeWithMixedContent(): void
    {
        $textContent = new Text('Please describe this image:');
        $imageContent = new ImageUrl('https://example.com/image.jpg');
        $message = new UserMessage($textContent, $imageContent);

        $expectedContent = [
            ['type' => 'text', 'text' => 'Please describe this image:'],
            ['type' => 'image', 'url' => 'https://example.com/image.jpg'],
        ];

        $innerNormalizer = $this->createMock(NormalizerInterface::class);
        $innerNormalizer->expects(self::once())
            ->method('normalize')
            ->with($message->content, null, [])
            ->willReturn($expectedContent);

        $this->normalizer->setNormalizer($innerNormalizer);

        $expected = [
            'role' => 'user',
            'content' => $expectedContent,
        ];

        self::assertSame($expected, $this->normalizer->normalize($message));
    }
}
