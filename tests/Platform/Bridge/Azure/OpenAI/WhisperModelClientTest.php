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
            new MockResponse('{"text": "Hello World"}'),
        ]);
        
        $client = new WhisperModelClient(
            $httpClient,
            'test.openai.azure.com',
            'whisper-deployment',
            '2023-12-01-preview',
            'test-key'
        );
        $model = new Whisper();
        $payload = ['file' => 'audio-data'];

        $client->request($model, $payload);

        $requestInfo = $httpClient->getRequestsCount() > 0 ? 
            $httpClient->getRequestsHistory()[0] : null;
        
        self::assertNotNull($requestInfo);
        self::assertSame('POST', $requestInfo['method']);
        self::assertStringContains('/audio/transcriptions', $requestInfo['url']);
    }

    #[Test]
    public function itUsesTranscriptionEndpointWhenTaskIsSpecified(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"text": "Hello World"}'),
        ]);
        
        $client = new WhisperModelClient(
            $httpClient,
            'test.openai.azure.com',
            'whisper-deployment',
            '2023-12-01-preview',
            'test-key'
        );
        $model = new Whisper();
        $payload = ['file' => 'audio-data'];
        $options = ['task' => Task::TRANSCRIPTION];

        $client->request($model, $payload, $options);

        $requestInfo = $httpClient->getRequestsCount() > 0 ? 
            $httpClient->getRequestsHistory()[0] : null;
        
        self::assertNotNull($requestInfo);
        self::assertSame('POST', $requestInfo['method']);
        self::assertStringContains('/audio/transcriptions', $requestInfo['url']);
    }

    #[Test]
    public function itUsesTranslationEndpointWhenTaskIsSpecified(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse('{"text": "Hello World"}'),
        ]);
        
        $client = new WhisperModelClient(
            $httpClient,
            'test.openai.azure.com',
            'whisper-deployment',
            '2023-12-01-preview',
            'test-key'
        );
        $model = new Whisper();
        $payload = ['file' => 'audio-data'];
        $options = ['task' => Task::TRANSLATION];

        $client->request($model, $payload, $options);

        $requestInfo = $httpClient->getRequestsCount() > 0 ? 
            $httpClient->getRequestsHistory()[0] : null;
        
        self::assertNotNull($requestInfo);
        self::assertSame('POST', $requestInfo['method']);
        self::assertStringContains('/audio/translations', $requestInfo['url']);
    }
}