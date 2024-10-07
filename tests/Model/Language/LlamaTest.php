<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Language;

use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\Content\Image;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\Message\UserMessage;
use PhpLlm\LlmChain\Model\Language\Llama;
use PhpLlm\LlmChain\Platform\Ollama;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

#[CoversClass(Llama::class)]
#[Small]
final class LlamaTest extends TestCase
{
    #[Test]
    public function convertMessages(): void
    {
        $messageBag = new MessageBag(
        );
        foreach (self::provideMessages() as $message) {
            $messageBag->append($message[1]);
        }

        $llama = new Llama(new Ollama(new MockHttpClient(), 'http://example.com'));

        self::assertSame(<<<EXPECTED
<|begin_of_text|><|start_header_id|>system<|end_header_id|>

You are a helpful chatbot.<|eot_id|>

<|start_header_id|>user<|end_header_id|>

Hello, how are you?<|eot_id|>

<|start_header_id|>user<|end_header_id|>

Hello, how are you?
What is your name?<|eot_id|>

<|start_header_id|>user<|end_header_id|>

Hello, how are you?
What is your name?
https://example.com/image.jpg<|eot_id|>

<|start_header_id|>assistant<|end_header_id|>

I am an assistant.<|eot_id|>
EXPECTED,
            $llama->convertToPrompt($messageBag)
        );
    }

    #[Test]
    #[DataProvider('provideMessages')]
    public function convertMessage(string $expected, UserMessage|SystemMessage|AssistantMessage $message): void
    {
        self::assertSame(
            $expected,
            (new Llama(new Ollama(new MockHttpClient(), 'http://example.com')))->convertMessage($message)
        );
    }

    /**
     * @return iterable<array{string, UserMessage|SystemMessage|AssistantMessage}>
     */
    public static function provideMessages(): iterable
    {
        yield 'System message' => [
            <<<SYSTEM
<|begin_of_text|><|start_header_id|>system<|end_header_id|>

You are a helpful chatbot.<|eot_id|>
SYSTEM,
            Message::forSystem('You are a helpful chatbot.'),
        ];

        yield 'UserMessage' => [
            <<<USER
<|start_header_id|>user<|end_header_id|>

Hello, how are you?<|eot_id|>
USER,
            Message::ofUser('Hello, how are you?'),
        ];

        yield 'UserMessage with two texts' => [
            <<<USER
<|start_header_id|>user<|end_header_id|>

Hello, how are you?
What is your name?<|eot_id|>
USER,
            Message::ofUser('Hello, how are you?', 'What is your name?'),
        ];

        yield 'UserMessage with two texts and one image' => [
            <<<USER
<|start_header_id|>user<|end_header_id|>

Hello, how are you?
What is your name?
https://example.com/image.jpg<|eot_id|>
USER,
            Message::ofUser('Hello, how are you?', 'What is your name?', new Image('https://example.com/image.jpg')),
        ];

        yield 'AssistantMessage' => [
            <<<ASSISTANT
<|start_header_id|>assistant<|end_header_id|>

I am an assistant.<|eot_id|>
ASSISTANT,
            new AssistantMessage('I am an assistant.'),
        ];

        yield 'AssistantMessage with null content' => [
            '',
            new AssistantMessage(),
        ];
    }
}
