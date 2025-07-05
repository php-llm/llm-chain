<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Response;

use PhpLlm\LlmChain\Platform\Response\BaseResponse;
use PhpLlm\LlmChain\Platform\Response\Exception\RawResponseAlreadySetException;
use PhpLlm\LlmChain\Platform\Response\Metadata\Metadata;
use PhpLlm\LlmChain\Platform\Response\RawHttpResponse;
use PhpLlm\LlmChain\Platform\Response\RawResponseInterface;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;
use PhpLlm\LlmChain\Platform\Response\ResponsePromise;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

#[CoversClass(ResponsePromise::class)]
#[UsesClass(Metadata::class)]
#[UsesClass(TextResponse::class)]
#[UsesClass(RawResponseAlreadySetException::class)]
#[Small]
final class ResponsePromiseTest extends TestCase
{
    #[Test]
    public function itUnwrapsTheResponseWhenGettingContent(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);
        $textResponse = new TextResponse('test content');

        $responseConverter = self::createMock(ResponseConverterInterface::class);
        $responseConverter->expects(self::once())
            ->method('convert')
            ->with($httpResponse, [])
            ->willReturn($textResponse);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), new RawHttpResponse($httpResponse));

        self::assertSame('test content', $responsePromise->getResponse()->getContent());
    }

    #[Test]
    public function itConvertsTheResponseOnlyOnce(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);
        $textResponse = new TextResponse('test content');

        $responseConverter = self::createMock(ResponseConverterInterface::class);
        $responseConverter->expects(self::once())
            ->method('convert')
            ->with($httpResponse, [])
            ->willReturn($textResponse);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), new RawHttpResponse($httpResponse));

        // Call unwrap multiple times, but the converter should only be called once
        $responsePromise->await();
        $responsePromise->await();
        $responsePromise->getResponse();
    }

    #[Test]
    public function itGetsRawResponseDirectly(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);
        $responseConverter = $this->createStub(ResponseConverterInterface::class);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), new RawHttpResponse($httpResponse));

        self::assertSame($httpResponse, $responsePromise->getRawResponse()->getRawObject());
    }

    #[Test]
    public function itSetsRawResponseOnUnwrappedResponseWhenNeeded(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);

        $unwrappedResponse = $this->createResponse(null);

        $responseConverter = $this->createStub(ResponseConverterInterface::class);
        $responseConverter->method('convert')->willReturn($unwrappedResponse);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), new RawHttpResponse($httpResponse));
        $responsePromise->await();

        // The raw response in the model response is now set and not null anymore
        self::assertSame($httpResponse, $unwrappedResponse->getRawResponse()->getRawObject());
    }

    #[Test]
    public function itDoesNotSetRawResponseOnUnwrappedResponseWhenAlreadySet(): void
    {
        $originHttpResponse = $this->createStub(SymfonyHttpResponse::class);
        $anotherHttpResponse = $this->createStub(SymfonyHttpResponse::class);

        $unwrappedResponse = $this->createResponse($anotherHttpResponse);

        $responseConverter = $this->createStub(ResponseConverterInterface::class);
        $responseConverter->method('convert')->willReturn($unwrappedResponse);

        $responsePromise = new ResponsePromise($responseConverter->convert(...), new RawHttpResponse($originHttpResponse));
        $responsePromise->await();

        // It is still the same raw response as set initially and so not overwritten
        self::assertSame($anotherHttpResponse, $unwrappedResponse->getRawResponse()->getRawObject());
    }

    /**
     * Workaround for low deps because mocking the ResponseInterface leads to an exception with
     * mock creation "Type Traversable|object|array|string|null contains both object and a class type"
     * in PHPUnit MockClass.
     */
    private function createResponse(?SymfonyHttpResponse $httpResponse): ResponseInterface
    {
        $rawResponse = null !== $httpResponse ? new RawHttpResponse($httpResponse) : null;

        return new class($rawResponse) extends BaseResponse {
            public function __construct(protected ?RawResponseInterface $rawResponse)
            {
            }

            public function getContent(): string
            {
                return 'test content';
            }
        };
    }

    #[Test]
    public function itPassesOptionsToConverter(): void
    {
        $httpResponse = $this->createStub(SymfonyHttpResponse::class);
        $options = ['option1' => 'value1', 'option2' => 'value2'];

        $responseConverter = self::createMock(ResponseConverterInterface::class);
        $responseConverter->expects(self::once())
            ->method('convert')
            ->with($httpResponse, $options)
            ->willReturn($this->createResponse(null));

        $responsePromise = new ResponsePromise($responseConverter->convert(...), new RawHttpResponse($httpResponse), $options);
        $responsePromise->await();
    }
}
