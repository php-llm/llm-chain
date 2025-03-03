<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Bridge\OpenAI\Embeddings;

use PhpLlm\LlmChain\Bridge\OpenAI\Embeddings\ResponseConverter;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(ResponseConverter::class)]
#[Small]
#[UsesClass(Vector::class)]
#[UsesClass(VectorResponse::class)]
class ResponseConverterTest extends TestCase
{
    #[Test]
    public function itConvertsAResponseToAVectorResponse(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('toArray')
            ->willReturn(\json_decode($this->getEmbeddingStub(), true));

        $vectorResponse = (new ResponseConverter())->convert($response);
        $convertedContent = $vectorResponse->getContent();

        self::assertCount(2, $convertedContent);

        self::assertSame([0.3, 0.4, 0.4], $convertedContent[0]->getData());
        self::assertSame([0.0, 0.0, 0.2], $convertedContent[1]->getData());
    }

    private function getEmbeddingStub(): string
    {
        return <<<'JSON'
        {
          "object": "list",
          "data": [
            {
              "object": "embedding",
              "index": 0,
              "embedding": [0.3, 0.4, 0.4]
            },
            {
              "object": "embedding",
              "index": 1,
              "embedding": [0.0, 0.0, 0.2]
            }
          ]
        }
        JSON;
    }
}
