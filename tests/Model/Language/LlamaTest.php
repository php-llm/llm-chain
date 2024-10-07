<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Model\Language;

use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\MessageInterface;
use PhpLlm\LlmChain\Message\SystemMessage;
use PhpLlm\LlmChain\Message\UserMessage;
use PhpLlm\LlmChain\Model\Language\Llama;
use PhpLlm\LlmChain\Platform\Ollama;
use PhpLlm\LlmChain\Response\Choice;
use PhpLlm\LlmChain\Response\ChoiceResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

#[CoversClass(Llama::class)]
#[Small]
final class LlamaTest extends TestCase
{
    #[Test]
    #[DataProvider('provideMessages')]
    public function convertMessage(string $expected, UserMessage|SystemMessage|AssistantMessage $message): void
    {
        (new Llama(new Ollama(new MockHttpClient(), 'http://example.com')))->convertMessage($message);
    }

    public static function provideMessages(): iterable
    {
        yield 'User message' => [
            'expected' => <<<USER
<|begin_of_text|><|start_header_id|>user<|end_header_id|>
Hello, how are you?
USER,
            'message' => new UserMessage(new TextConten'Hello, how are you?'),
        ];

        yield 'System message' => [
            'expected' => <<<SYSTEM
USER;

        
    }

    #[Test]
    public function choiceResponseWithNoChoices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Response must have at least one choice.');

        new ChoiceResponse();
    }
}
