<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Response;

use PhpLlm\LlmChain\Model\Response\BaseResponse;
use PhpLlm\LlmChain\Model\Response\Exception\RawResponseAlreadySet;
use PhpLlm\LlmChain\Model\Response\Metadata\MetadataAwareTrait;
use PhpLlm\LlmChain\Model\Response\RawResponseAwareTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

#[CoversClass(BaseResponse::class)]
#[UsesTrait(MetadataAwareTrait::class)]
#[UsesTrait(RawResponseAwareTrait::class)]
#[Small]
final class BaseResponseTest extends TestCase
{
    #[Test]
    public function itCanHandleMetadata(): void
    {
        $response = $this->createResponse();
        $metadata = $response->getMetadata();

        self::assertCount(0, $metadata);

        $metadata->add('key', 'value');
        $metadata = $response->getMetadata();

        self::assertCount(1, $metadata);
    }

    #[Test]
    public function itCanBeEnrichedWithARawResponse(): void
    {
        $response = $this->createResponse();
        $rawResponse = $this->createMock(SymfonyHttpResponse::class);

        $response->setRawResponse($rawResponse);
        self::assertSame($rawResponse, $response->getRawResponse());
    }

    #[Test]
    public function itThrowsAnExceptionWhenSettingARawResponseTwice(): void
    {
        $this->expectException(RawResponseAlreadySet::class);

        $response = $this->createResponse();
        $rawResponse = $this->createMock(SymfonyHttpResponse::class);

        $response->setRawResponse($rawResponse);
        $response->setRawResponse($rawResponse);
    }

    private function createResponse(): BaseResponse
    {
        return new class extends BaseResponse {
            public function getContent(): string
            {
                return 'test';
            }
        };
    }
}
