<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\InputProcessor;

use PhpLlm\LlmChain\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor\SystemPromptInputProcessor;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Message\SystemMessage;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SystemPromptInputProcessor::class)]
#[UsesClass(GPT::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBag::class)]
#[Small]
final class SystemPromptInputProcessorTest extends TestCase
{
    #[Test]
    public function processInputAddsSystemMessageWhenNoneExists(): void
    {
        $processor = new SystemPromptInputProcessor('This is a system prompt');

        $input = new Input(new GPT(), new MessageBag(Message::ofUser('This is a user message')), []);
        $processor->processInput($input);

        $messages = $input->messages->getMessages();
        self::assertCount(2, $messages);
        self::assertInstanceOf(SystemMessage::class, $messages[0]);
        self::assertInstanceOf(UserMessage::class, $messages[1]);
        self::assertSame('This is a system prompt', $messages[0]->content);
    }

    #[Test]
    public function processInputDoesNotAddSystemMessageWhenOneExists(): void
    {
        $processor = new SystemPromptInputProcessor('This is a system prompt');

        $messages = new MessageBag(
            Message::forSystem('This is already a system prompt'),
            Message::ofUser('This is a user message'),
        );
        $input = new Input(new GPT(), $messages, []);
        $processor->processInput($input);

        $messages = $input->messages->getMessages();
        self::assertCount(2, $messages);
        self::assertInstanceOf(SystemMessage::class, $messages[0]);
        self::assertInstanceOf(UserMessage::class, $messages[1]);
        self::assertSame('This is already a system prompt', $messages[0]->content);
    }
}
