<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\Memory;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\Memory\Memory;
use PhpLlm\LlmChain\Chain\Memory\StaticMemoryProvider;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StaticMemoryProvider::class)]
#[UsesClass(Input::class)]
#[UsesClass(Memory::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(Model::class)]
#[Small]
final class StaticMemoryProviderTest extends TestCase
{
    #[Test]
    public function itsReturnsNullWhenNoFactsAreProvided(): void
    {
        $provider = new StaticMemoryProvider();

        $memory = $provider->loadMemory(new Input(
            self::createStub(Model::class),
            new MessageBag(),
            []
        ));

        self::assertCount(0, $memory);
    }

    #[Test]
    public function itDeliversFormattedFacts(): void
    {
        $provider = new StaticMemoryProvider(
            $fact1 = 'The sky is blue',
            $fact2 = 'Water is wet',
        );

        $memory = $provider->loadMemory(new Input(
            self::createStub(Model::class),
            new MessageBag(),
            []
        ));

        self::assertCount(1, $memory);
        self::assertInstanceOf(Memory::class, $memory[0]);
        $expectedContent = "## Static Memory\n\n- {$fact1}\n- {$fact2}";
        self::assertSame($expectedContent, $memory[0]->content);
    }
}
