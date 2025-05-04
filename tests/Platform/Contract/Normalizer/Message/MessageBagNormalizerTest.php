<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Normalizer\Message;

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\MessageBagInterface;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\Message\MessageBagNormalizer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[CoversClass(MessageBagNormalizer::class)]
final class MessageBagNormalizerTest extends TestCase
{
    private MessageBagNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new MessageBagNormalizer();
    }

    #[Test]
    public function supportsNormalization(): void
    {
        $messageBag = $this->createMock(MessageBagInterface::class);

        self::assertTrue($this->normalizer->supportsNormalization($messageBag));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    #[Test]
    public function getSupportedTypes(): void
    {
        self::assertSame([MessageBagInterface::class => true], $this->normalizer->getSupportedTypes(null));
    }

    #[Test]
    public function normalizeWithoutModel(): void
    {
        $messages = [
            new SystemMessage('You are a helpful assistant'),
            new UserMessage(new Text('Hello')),
        ];

        $messageBag = new MessageBag(...$messages);

        $innerNormalizer = $this->createMock(NormalizerInterface::class);
        $innerNormalizer->expects(self::once())
            ->method('normalize')
            ->with($messages, null, [])
            ->willReturn([
                ['role' => 'system', 'content' => 'You are a helpful assistant'],
                ['role' => 'user', 'content' => 'Hello'],
            ]);

        $this->normalizer->setNormalizer($innerNormalizer);

        $expected = [
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant'],
                ['role' => 'user', 'content' => 'Hello'],
            ],
        ];

        self::assertSame($expected, $this->normalizer->normalize($messageBag));
    }

    #[Test]
    public function normalizeWithModel(): void
    {
        $messages = [
            new SystemMessage('You are a helpful assistant'),
            new UserMessage(new Text('Hello')),
        ];

        $messageBag = new MessageBag(...$messages);

        $innerNormalizer = $this->createMock(NormalizerInterface::class);
        $innerNormalizer->expects(self::once())
            ->method('normalize')
            ->with($messages, null, [Contract::CONTEXT_MODEL => new GPT()])
            ->willReturn([
                ['role' => 'system', 'content' => 'You are a helpful assistant'],
                ['role' => 'user', 'content' => 'Hello'],
            ]);

        $this->normalizer->setNormalizer($innerNormalizer);

        $expected = [
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant'],
                ['role' => 'user', 'content' => 'Hello'],
            ],
            'model' => 'gpt-4o',
        ];

        self::assertSame($expected, $this->normalizer->normalize($messageBag, context: [
            Contract::CONTEXT_MODEL => new GPT(),
        ]));
    }
}
