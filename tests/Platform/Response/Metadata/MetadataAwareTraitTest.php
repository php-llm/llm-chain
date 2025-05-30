<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Response\Metadata;

use PhpLlm\LlmChain\Platform\Response\Metadata\Metadata;
use PhpLlm\LlmChain\Platform\Response\Metadata\MetadataAwareTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversTrait(MetadataAwareTrait::class)]
#[Small]
#[UsesClass(Metadata::class)]
final class MetadataAwareTraitTest extends TestCase
{
    #[Test]
    public function itCanHandleMetadata(): void
    {
        $response = $this->createTestClass();
        $metadata = $response->getMetadata();

        self::assertCount(0, $metadata);

        $metadata->add('key', 'value');
        $metadata = $response->getMetadata();

        self::assertCount(1, $metadata);
    }

    private function createTestClass(): object
    {
        return new class {
            use MetadataAwareTrait;
        };
    }
}
