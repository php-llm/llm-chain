<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Contract\Denormalizer;

use PhpLlm\LlmChain\Platform\Contract\Denormalizer\TextResponseDenormalizer;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\ResponseContract;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TextResponseDenormalizer::class)]
final class TextResponseDenormalizerTest extends TestCase
{
    private TextResponseDenormalizer $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new TextResponseDenormalizer();
    }

    public function testSupportsDenormalizationForNonStreamingResponse(): void
    {
        $context = [ResponseContract::CONTEXT_OPTIONS => []];

        $result = $this->denormalizer->supportsDenormalization([], LlmResponse::class, null, $context);

        self::assertTrue($result);
    }

    public function testDoesNotSupportDenormalizationForStreamingResponse(): void
    {
        $context = [ResponseContract::CONTEXT_OPTIONS => ['stream' => true]];

        $result = $this->denormalizer->supportsDenormalization([], LlmResponse::class, null, $context);

        self::assertFalse($result);
    }

    public function testGetSupportedTypes(): void
    {
        $supportedTypes = $this->denormalizer->getSupportedTypes(null);

        self::assertSame([LlmResponse::class => false], $supportedTypes);
    }
}
