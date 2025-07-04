<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\OpenAI;

use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\TokenOutputProcessor;
use PhpLlm\LlmChain\Platform\Message\MessageBagInterface;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\Metadata\Metadata;
use PhpLlm\LlmChain\Platform\Response\RawHttpResponse;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;
use PhpLlm\LlmChain\Platform\Response\StreamResponse;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface as SymfonyHttpResponse;

#[CoversClass(TokenOutputProcessor::class)]
#[UsesClass(Output::class)]
#[UsesClass(TextResponse::class)]
#[UsesClass(StreamResponse::class)]
#[UsesClass(Metadata::class)]
#[Small]
final class TokenOutputProcessorTest extends TestCase
{
    #[Test]
    public function itHandlesStreamResponsesWithoutProcessing(): void
    {
        $processor = new TokenOutputProcessor();
        $streamResponse = new StreamResponse((static function () { yield 'test'; })());
        $output = $this->createOutput($streamResponse);

        $processor->processOutput($output);

        $metadata = $output->response->getMetadata();
        self::assertCount(0, $metadata);
    }

    #[Test]
    public function itDoesNothingWithoutRawResponse(): void
    {
        $processor = new TokenOutputProcessor();
        $textResponse = new TextResponse('test');
        $output = $this->createOutput($textResponse);

        $processor->processOutput($output);

        $metadata = $output->response->getMetadata();
        self::assertCount(0, $metadata);
    }

    #[Test]
    public function itAddsRemainingTokensToMetadata(): void
    {
        $processor = new TokenOutputProcessor();
        $textResponse = new TextResponse('test');

        $textResponse->setRawResponse($this->createRawResponse());

        $output = $this->createOutput($textResponse);

        $processor->processOutput($output);

        $metadata = $output->response->getMetadata();
        self::assertCount(1, $metadata);
        self::assertSame(1000, $metadata->get('remaining_tokens'));
    }

    #[Test]
    public function itAddsUsageTokensToMetadata(): void
    {
        $processor = new TokenOutputProcessor();
        $textResponse = new TextResponse('test');

        $rawResponse = $this->createRawResponse([
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 20,
                'total_tokens' => 30,
            ],
        ]);

        $textResponse->setRawResponse($rawResponse);

        $output = $this->createOutput($textResponse);

        $processor->processOutput($output);

        $metadata = $output->response->getMetadata();
        self::assertCount(4, $metadata);
        self::assertSame(1000, $metadata->get('remaining_tokens'));
        self::assertSame(10, $metadata->get('prompt_tokens'));
        self::assertSame(20, $metadata->get('completion_tokens'));
        self::assertSame(30, $metadata->get('total_tokens'));
    }

    #[Test]
    public function itHandlesMissingUsageFields(): void
    {
        $processor = new TokenOutputProcessor();
        $textResponse = new TextResponse('test');

        $rawResponse = $this->createRawResponse([
            'usage' => [
                // Missing some fields
                'prompt_tokens' => 10,
            ],
        ]);

        $textResponse->setRawResponse($rawResponse);

        $output = $this->createOutput($textResponse);

        $processor->processOutput($output);

        $metadata = $output->response->getMetadata();
        self::assertCount(4, $metadata);
        self::assertSame(1000, $metadata->get('remaining_tokens'));
        self::assertSame(10, $metadata->get('prompt_tokens'));
        self::assertNull($metadata->get('completion_tokens'));
        self::assertNull($metadata->get('total_tokens'));
    }

    private function createRawResponse(array $data = []): RawHttpResponse
    {
        $rawResponse = self::createStub(SymfonyHttpResponse::class);
        $rawResponse->method('getHeaders')->willReturn([
            'x-ratelimit-remaining-tokens' => ['1000'],
        ]);
        $rawResponse->method('toArray')->willReturn($data);

        return new RawHttpResponse($rawResponse);
    }

    private function createOutput(ResponseInterface $response): Output
    {
        return new Output(
            self::createStub(Model::class),
            $response,
            self::createStub(MessageBagInterface::class),
            [],
        );
    }
}
