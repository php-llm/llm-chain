<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\BaseResponse;
use PhpLlm\LlmChain\Platform\Response\Exception\RawResponseAlreadySetException;
use PhpLlm\LlmChain\Platform\Response\Metadata\Metadata;
use PhpLlm\LlmChain\Platform\Response\Metadata\MetadataAwareTrait;
use PhpLlm\LlmChain\Platform\Response\RawResponseAwareTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

#[CoversClass(BaseResponse::class)]
#[UsesTrait(MetadataAwareTrait::class)]
#[UsesTrait(RawResponseAwareTrait::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(RawResponseAlreadySetException::class)]
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
        $rawResponse = self::createMock(SymfonyHttpResponse::class);

        $response->setRawResponse($rawResponse);
        self::assertSame($rawResponse, $response->getRawResponse());
    }

    #[Test]
    public function itThrowsAnExceptionWhenSettingARawResponseTwice(): void
    {
        self::expectException(RawResponseAlreadySetException::class);

        $response = $this->createResponse();
        $rawResponse = self::createMock(SymfonyHttpResponse::class);

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
