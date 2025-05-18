<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\SystemMessageNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SystemMessageNormalizer::class)]
final class SystemMessageNormalizerTest extends TestCase
{
    private SystemMessageNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new SystemMessageNormalizer();
    }

    #[Test]
    public function supportsNormalization(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new SystemMessage('content')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        self::assertSame([SystemMessage::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalize(): void
    {
        $message = new SystemMessage('You are a helpful assistant');

        $expected = [
            'role' => 'system',
            'content' => 'You are a helpful assistant',
        ];

        self::assertSame($expected, $this->normalizer->normalize($message));
    }
}
