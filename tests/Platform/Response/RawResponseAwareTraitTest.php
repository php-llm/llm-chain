<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\Exception\RawResponseAlreadySetException;
use PhpLlm\LlmChain\Platform\Response\RawResponseAwareTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

#[CoversTrait(RawResponseAwareTrait::class)]
#[Small]
#[UsesClass(RawResponseAlreadySetException::class)]
final class RawResponseAwareTraitTest extends TestCase
{
    #[Test]
    public function itCanBeEnrichedWithARawResponse(): void
    {
        $response = $this->createTestClass();
        $rawResponse = self::createMock(SymfonyHttpResponse::class);

        $response->setRawResponse($rawResponse);
        self::assertSame($rawResponse, $response->getRawResponse());
    }

    #[Test]
    public function itThrowsAnExceptionWhenSettingARawResponseTwice(): void
    {
        self::expectException(RawResponseAlreadySetException::class);

        $response = $this->createTestClass();
        $rawResponse = self::createMock(SymfonyHttpResponse::class);

        $response->setRawResponse($rawResponse);
        $response->setRawResponse($rawResponse);
    }

    private function createTestClass(): object
    {
        return new class {
            use RawResponseAwareTrait;
        };
    }
}
