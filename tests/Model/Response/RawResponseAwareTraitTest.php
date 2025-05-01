<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Response;

use PhpLlm\LlmChain\Model\Response\Exception\RawResponseAlreadySet;
use PhpLlm\LlmChain\Model\Response\RawResponseAwareTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

#[CoversTrait(RawResponseAwareTrait::class)]
#[Small]
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
        self::expectException(RawResponseAlreadySet::class);

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
