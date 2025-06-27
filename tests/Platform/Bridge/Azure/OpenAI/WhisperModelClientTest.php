<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\Azure\OpenAI;

use PhpLlm\LlmChain\Platform\Bridge\Azure\OpenAI\WhisperModelClient;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

#[CoversClass(WhisperModelClient::class)]
#[Small]
final class WhisperModelClientTest extends TestCase
{
    #[Test]
    public function itSupportsWhisperModel(): void
    {
        $client = new WhisperModelClient(
            new MockHttpClient(),
            'test.openai.azure.com',
            'whisper-deployment',
            '2023-12-01-preview',
            'test-key'
        );
        $model = new Whisper();

        self::assertTrue($client->supports($model));
    }

    #[Test]
    public function itUsesTranscriptionEndpointByDefault(): void
    {
        $httpClient = new MockHttpClient([
            function ($method, $url): MockResponse {
                self::assertSame('POST', $method);
                self::assertSame('https://test.azure.com/openai/deployments/whspr/audio/transcriptions?api-version=2023-12', $url);

                return new MockResponse('{"text": "Hello World"}');
            },
        ]);

        $client = new WhisperModelClient($httpClient, 'test.azure.com', 'whspr', '2023-12', 'test-key');
        $model = new Whisper();
        $payload = ['file' => 'audio-data'];

        $client->request($model, $payload);

        self::assertSame(1, $httpClient->getRequestsCount());
    }

    #[Test]
    public function itUsesTranscriptionEndpointWhenTaskIsSpecified(): void
    {
        $httpClient = new MockHttpClient([
            function ($method, $url): MockResponse {
                self::assertSame('POST', $method);
                self::assertSame('https://test.azure.com/openai/deployments/whspr/audio/transcriptions?api-version=2023-12', $url);

                return new MockResponse('{"text": "Hello World"}');
            },
        ]);

        $client = new WhisperModelClient($httpClient, 'test.azure.com', 'whspr', '2023-12', 'test-key');
        $model = new Whisper();
        $payload = ['file' => 'audio-data'];
        $options = ['task' => Task::TRANSCRIPTION];

        $client->request($model, $payload, $options);

        self::assertSame(1, $httpClient->getRequestsCount());
    }

    #[Test]
    public function itUsesTranslationEndpointWhenTaskIsSpecified(): void
    {
        $httpClient = new MockHttpClient([
            function ($method, $url): MockResponse {
                self::assertSame('POST', $method);
                self::assertSame('https://test.azure.com/openai/deployments/whspr/audio/translations?api-version=2023-12', $url);

                return new MockResponse('{"text": "Hello World"}');
            },
        ]);

        $client = new WhisperModelClient($httpClient, 'test.azure.com', 'whspr', '2023-12', 'test-key');
        $model = new Whisper();
        $payload = ['file' => 'audio-data'];
        $options = ['task' => Task::TRANSLATION];

        $client->request($model, $payload, $options);

        self::assertSame(1, $httpClient->getRequestsCount());
    }
}
