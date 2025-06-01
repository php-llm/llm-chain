<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform;

use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Model::class)]
#[Small]
#[UsesClass(Capability::class)]
final class ModelTest extends TestCase
{
    #[Test]
    public function returnsName(): void
    {
        $model = new Model('gpt-4');

        self::assertSame('gpt-4', $model->getName());
    }

    #[Test]
    public function returnsCapabilities(): void
    {
        $model = new Model('gpt-4', [Capability::INPUT_TEXT, Capability::OUTPUT_TEXT]);

        self::assertSame([Capability::INPUT_TEXT, Capability::OUTPUT_TEXT], $model->getCapabilities());
    }

    #[Test]
    public function checksSupportForCapability(): void
    {
        $model = new Model('gpt-4', [Capability::INPUT_TEXT, Capability::OUTPUT_TEXT]);

        self::assertTrue($model->supports(Capability::INPUT_TEXT));
        self::assertTrue($model->supports(Capability::OUTPUT_TEXT));
        self::assertFalse($model->supports(Capability::INPUT_IMAGE));
    }

    #[Test]
    public function returnsEmptyCapabilitiesByDefault(): void
    {
        $model = new Model('gpt-4');

        self::assertSame([], $model->getCapabilities());
    }

    #[Test]
    public function returnsOptions(): void
    {
        $options = [
            'temperature' => 0.7,
            'max_tokens' => 1024,
        ];
        $model = new Model('gpt-4', [], $options);

        self::assertSame($options, $model->getOptions());
    }

    #[Test]
    public function returnsEmptyOptionsByDefault(): void
    {
        $model = new Model('gpt-4');

        self::assertSame([], $model->getOptions());
    }
}
