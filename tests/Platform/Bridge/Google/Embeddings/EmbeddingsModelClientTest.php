<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Google\Embeddings;

use PhpLlm\LlmChain\Platform\Bridge\Google\Embeddings;
use PhpLlm\LlmChain\Platform\Bridge\Google\Embeddings\ModelClient;
use PhpLlm\LlmChain\Platform\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(ModelClient::class)]
#[Small]
#[UsesClass(Vector::class)]
#[UsesClass(VectorResponse::class)]
#[UsesClass(Embeddings::class)]
final class EmbeddingsModelClientTest extends TestCase
{
    #[Test]
    public function itMakesARequestWithCorrectPayload(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('toArray')
            ->willReturn(json_decode($this->getEmbeddingStub(), true));

        $httpClient = self::createMock(HttpClientInterface::class);
        $httpClient->expects(self::once())
            ->method('request')
            ->with(
                'POST',
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-exp-03-07:batchEmbedContents',
                [
                    'headers' => ['x-goog-api-key' => 'test'],
                    'json' => [
                        'requests' => [
                            [
                                'model' => 'models/gemini-embedding-exp-03-07',
                                'content' => ['parts' => [['text' => 'payload1']]],
                                'outputDimensionality' => 1536,
                                'taskType' => 'CLASSIFICATION',
                            ],
                            [
                                'model' => 'models/gemini-embedding-exp-03-07',
                                'content' => ['parts' => [['text' => 'payload2']]],
                                'outputDimensionality' => 1536,
                                'taskType' => 'CLASSIFICATION',
                            ],
                        ],
                    ],
                ],
            )
            ->willReturn($response);

        $model = new Embeddings(Embeddings::GEMINI_EMBEDDING_EXP_03_07, ['dimensions' => 1536, 'task_type' => 'CLASSIFICATION']);

        $httpResponse = (new ModelClient($httpClient, 'test'))->request($model, ['payload1', 'payload2']);
        self::assertSame(json_decode($this->getEmbeddingStub(), true), $httpResponse->toArray());
    }

    #[Test]
    public function itConvertsAResponseToAVectorResponse(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('toArray')
            ->willReturn(json_decode($this->getEmbeddingStub(), true));

        $httpClient = self::createMock(HttpClientInterface::class);

        $vectorResponse = (new ModelClient($httpClient, 'test'))->convert($response);
        $convertedContent = $vectorResponse->getContent();

        self::assertCount(2, $convertedContent);

        self::assertSame([0.3, 0.4, 0.4], $convertedContent[0]->getData());
        self::assertSame([0.0, 0.0, 0.2], $convertedContent[1]->getData());
    }

    private function getEmbeddingStub(): string
    {
        return <<<'JSON'
            {
              "embeddings": [
                {
                  "values": [0.3, 0.4, 0.4]
                },
                {
                  "values": [0.0, 0.0, 0.2]
                }
              ]
            }
            JSON;
    }
}
