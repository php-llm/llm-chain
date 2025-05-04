<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Bridge\Google\Contract;

use PhpLlm\LlmChain\Bridge\Google\Contract\UserMessageNormalizer;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversClass(UserMessageNormalizer::class)]
final class UserMessageNormalizerTest extends TestCase
{
    #[Test]
    public function supportsNormalization(): void
    {
        $normalizer = new UserMessageNormalizer();

        self::assertTrue($normalizer->supportsNormalization(new UserMessage(new Text('Hello'))));
        self::assertFalse($normalizer->supportsNormalization('not a user message'));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        $normalizer = new UserMessageNormalizer();

        self::assertSame([UserMessage::class => true], $normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalizeTextContent(): void
    {
        $normalizer = new UserMessageNormalizer();
        $message = new UserMessage(new Text('Write a story about a magic backpack.'));

        $normalized = $normalizer->normalize($message);

        self::assertSame([['text' => 'Write a story about a magic backpack.']], $normalized);
    }

    #[Test]
    public function normalizeImageContent(): void
    {
        $normalizer = new UserMessageNormalizer();
        $imageContent = Image::fromFile(dirname(__DIR__, 3).'/Fixture/image.jpg');
        $message = new UserMessage(new Text('Tell me about this instrument'), $imageContent);

        $normalized = $normalizer->normalize($message);

        self::assertCount(2, $normalized);
        self::assertSame(['text' => 'Tell me about this instrument'], $normalized[0]);
        self::assertArrayHasKey('inline_data', $normalized[1]);
        self::assertSame('image/jpeg', $normalized[1]['inline_data']['mime_type']);
        self::assertNotEmpty($normalized[1]['inline_data']['data']);

        // Verify that the base64 data string starts correctly for a JPEG
        self::assertStringStartsWith('/9j/', $normalized[1]['inline_data']['data']);
    }
}
