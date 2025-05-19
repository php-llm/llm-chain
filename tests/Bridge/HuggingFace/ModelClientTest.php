<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Bridge\HuggingFace;

use PhpLlm\LlmChain\Bridge\HuggingFace\Model;
use PhpLlm\LlmChain\Bridge\HuggingFace\ModelClient;
use PhpLlm\LlmChain\Bridge\HuggingFace\Task;
use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PhpLlm\LlmChain\Model\Model as BaseModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

#[CoversClass(ModelClient::class)]
#[Small]
#[UsesClass(Model::class)]
final class ModelClientTest extends TestCase
{
    public function testSupportsWithHuggingFaceModel(): void
    {
        $httpClient = new MockHttpClient();
        $modelClient = new ModelClient($httpClient, 'test-provider', 'test-api-key');
        $model = new Model('test-model');

        self::assertTrue($modelClient->supports($model, 'test-input'));
    }

    public function testSupportsWithNonHuggingFaceModel(): void
    {
        $httpClient = new MockHttpClient();
        $modelClient = new ModelClient($httpClient, 'test-provider', 'test-api-key');
        $model = self::createMock(BaseModel::class);

        self::assertFalse($modelClient->supports($model, 'test-input'));
    }

    public function testRequestWithUnsupportedInputType(): void
    {
        $httpClient = new MockHttpClient();
        $modelClient = new ModelClient($httpClient, 'test-provider', 'test-api-key');
        $model = new Model('test-model');

        self::expectException(\InvalidArgumentException::class);
        self::expectExceptionMessage('Unsupported input type: stdClass');

        $modelClient->request($model, new \stdClass());
    }

    public function testRequestWithNonHuggingFaceModel(): void
    {
        $httpClient = new MockHttpClient();
        $modelClient = new ModelClient($httpClient, 'test-provider', 'test-api-key');
        $model = self::createMock(BaseModel::class);

        self::expectException(\InvalidArgumentException::class);

        $modelClient->request($model, 'test input');
    }

    #[DataProvider('urlTestCases')]
    public function testGetUrlForDifferentInputsAndTasks(object|array|string $input, ?string $task, string $expectedUrl): void
    {
        $reflection = new \ReflectionClass(ModelClient::class);
        $getUrlMethod = $reflection->getMethod('getUrl');
        $getUrlMethod->setAccessible(true);

        $model = new Model('test-model');
        $httpClient = new MockHttpClient();
        $modelClient = new ModelClient($httpClient, 'test-provider', 'test-api-key');

        $actualUrl = $getUrlMethod->invoke($modelClient, $model, $input, $task);

        self::assertEquals($expectedUrl, $actualUrl);
    }

    public static function urlTestCases(): \Iterator
    {
        $messageBag = new MessageBag();
        $messageBag->add(new UserMessage(new Text('Test message')));
        yield 'string input' => [
            'input' => 'Hello world',
            'task' => null,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/models/test-model',
        ];
        yield 'array input' => [
            'input' => ['text' => 'Hello world'],
            'task' => null,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/models/test-model',
        ];
        yield 'image input' => [
            'input' => Image::fromDataUrl('data:image/jpeg;base64,/9j/Cg=='),
            'task' => null,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/models/test-model',
        ];
        yield 'feature extraction' => [
            'input' => 'Extract features',
            'task' => Task::FEATURE_EXTRACTION,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/pipeline/feature-extraction/test-model',
        ];
        yield 'message bag' => [
            'input' => $messageBag,
            'task' => null,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/models/test-model/v1/chat/completions',
        ];
    }

    #[DataProvider('payloadTestCases')]
    public function testGetPayloadForDifferentInputsAndTasks(object|array|string $input, ?string $task, array $options, array $expectedKeys, array $expectedValues = []): void
    {
        $reflection = new \ReflectionClass(ModelClient::class);
        $getPayloadMethod = $reflection->getMethod('getPayload');
        $getPayloadMethod->setAccessible(true);

        $httpClient = new MockHttpClient();
        $modelClient = new ModelClient($httpClient, 'test-provider', 'test-api-key');

        $payload = $getPayloadMethod->invoke($modelClient, $input, $options);

        // Check that expected keys exist
        foreach ($expectedKeys as $key) {
            self::assertArrayHasKey($key, $payload);
        }

        // Check expected values if specified
        foreach ($expectedValues as $path => $value) {
            $keys = explode('.', $path);
            $current = $payload;
            foreach ($keys as $key) {
                self::assertArrayHasKey($key, $current);
                $current = $current[$key];
            }

            self::assertEquals($value, $current);
        }
    }

    public static function payloadTestCases(): \Iterator
    {
        $messageBag = new MessageBag();
        $messageBag->add(new UserMessage(new Text('Test message')));
        yield 'string input' => [
            'input' => 'Hello world',
            'task' => null,
            'options' => [],
            'expectedKeys' => ['headers', 'json'],
            'expectedValues' => [
                'headers.Content-Type' => 'application/json',
                'json.inputs' => 'Hello world',
            ],
        ];
        yield 'array input' => [
            'input' => ['text' => 'Hello world'],
            'task' => null,
            'options' => ['temperature' => 0.7],
            'expectedKeys' => ['headers', 'json'],
            'expectedValues' => [
                'headers.Content-Type' => 'application/json',
                'json.inputs' => ['text' => 'Hello world'],
                'json.parameters.temperature' => 0.7,
            ],
        ];
        yield 'message bag' => [
            'input' => $messageBag,
            'task' => null,
            'options' => ['max_tokens' => 100],
            'expectedKeys' => ['headers', 'json'],
            'expectedValues' => [
                'headers.Content-Type' => 'application/json',
                'json.max_tokens' => 100,
            ],
        ];
    }
}
