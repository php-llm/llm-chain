<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\HuggingFace;

use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Contract\FileNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Contract\MessageBagNormalizer;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\ModelClient;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Task;
use PhpLlm\LlmChain\Platform\Contract;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Model;
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
    #[DataProvider('urlTestCases')]
    public function testGetUrlForDifferentInputsAndTasks(?string $task, string $expectedUrl): void
    {
        $reflection = new \ReflectionClass(ModelClient::class);
        $getUrlMethod = $reflection->getMethod('getUrl');
        $getUrlMethod->setAccessible(true);

        $model = new Model('test-model');
        $httpClient = new MockHttpClient();
        $modelClient = new ModelClient($httpClient, 'test-provider', 'test-api-key');

        $actualUrl = $getUrlMethod->invoke($modelClient, $model, $task);

        self::assertEquals($expectedUrl, $actualUrl);
    }

    public static function urlTestCases(): \Iterator
    {
        $messageBag = new MessageBag();
        $messageBag->add(new UserMessage(new Text('Test message')));
        yield 'string input' => [
            'task' => null,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/models/test-model',
        ];
        yield 'array input' => [
            'task' => null,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/models/test-model',
        ];
        yield 'image input' => [
            'task' => null,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/models/test-model',
        ];
        yield 'feature extraction' => [
            'task' => Task::FEATURE_EXTRACTION,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/pipeline/feature-extraction/test-model',
        ];
        yield 'message bag' => [
            'task' => Task::CHAT_COMPLETION,
            'expectedUrl' => 'https://router.huggingface.co/test-provider/models/test-model/v1/chat/completions',
        ];
    }

    #[DataProvider('payloadTestCases')]
    public function testGetPayloadForDifferentInputsAndTasks(object|array|string $input, array $options, array $expectedKeys, array $expectedValues = []): void
    {
        // Contract handling first
        $contract = Contract::create(
            new FileNormalizer(),
            new MessageBagNormalizer()
        );

        $payload = $contract->createRequestPayload(new Model('test-model'), $input);

        $reflection = new \ReflectionClass(ModelClient::class);
        $getPayloadMethod = $reflection->getMethod('getPayload');
        $getPayloadMethod->setAccessible(true);

        $httpClient = new MockHttpClient();
        $modelClient = new ModelClient($httpClient, 'test-provider', 'test-api-key');

        $actual = $getPayloadMethod->invoke($modelClient, $payload, $options);

        // Check that expected keys exist
        foreach ($expectedKeys as $key) {
            self::assertArrayHasKey($key, $actual);
        }

        // Check expected values if specified
        foreach ($expectedValues as $path => $value) {
            $keys = explode('.', $path);
            $current = $actual;
            foreach ($keys as $key) {
                self::assertArrayHasKey($key, $current);
                $current = $current[$key];
            }

            self::assertEquals($value, $current);
        }
    }

    public static function payloadTestCases(): \Iterator
    {
        yield 'string input' => [
            'input' => 'Hello world',
            'options' => [],
            'expectedKeys' => ['headers', 'json'],
            'expectedValues' => [
                'headers.Content-Type' => 'application/json',
                'json.inputs' => 'Hello world',
            ],
        ];

        yield 'array input' => [
            'input' => ['text' => 'Hello world'],
            'options' => ['temperature' => 0.7],
            'expectedKeys' => ['headers', 'json'],
            'expectedValues' => [
                'headers.Content-Type' => 'application/json',
                'json.inputs' => ['text' => 'Hello world'],
                'json.parameters.temperature' => 0.7,
            ],
        ];

        $messageBag = new MessageBag();
        $messageBag->add(new UserMessage(new Text('Test message')));

        yield 'message bag' => [
            'input' => $messageBag,
            'options' => ['max_tokens' => 100],
            'expectedKeys' => ['headers', 'json'],
            'expectedValues' => [
                'headers.Content-Type' => 'application/json',
                'json.max_tokens' => 100,
            ],
        ];
    }
}
