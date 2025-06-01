<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Chain\InputProcessor;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor\SystemPromptInputProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\ToolboxInterface;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Response\ToolCall;
use PhpLlm\LlmChain\Platform\Tool\ExecutionReference;
use PhpLlm\LlmChain\Platform\Tool\Tool;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolNoParams;
use PhpLlm\LlmChain\Tests\Fixture\Tool\ToolRequiredParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SystemPromptInputProcessor::class)]
#[UsesClass(GPT::class)]
#[UsesClass(Message::class)]
#[UsesClass(MessageBag::class)]
#[UsesClass(Input::class)]
#[UsesClass(SystemMessage::class)]
#[UsesClass(UserMessage::class)]
#[UsesClass(Text::class)]
#[UsesClass(Tool::class)]
#[UsesClass(ExecutionReference::class)]
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

    #[Test]
    public function doesNotIncludeToolsIfToolboxIsEmpty(): void
    {
        $processor = new SystemPromptInputProcessor(
            'This is a system prompt',
            new class implements ToolboxInterface {
                public function getTools(): array
                {
                    return [];
                }

                public function execute(ToolCall $toolCall): mixed
                {
                    return null;
                }
            }
        );

        $input = new Input(new GPT(), new MessageBag(Message::ofUser('This is a user message')), []);
        $processor->processInput($input);

        $messages = $input->messages->getMessages();
        self::assertCount(2, $messages);
        self::assertInstanceOf(SystemMessage::class, $messages[0]);
        self::assertInstanceOf(UserMessage::class, $messages[1]);
        self::assertSame('This is a system prompt', $messages[0]->content);
    }

    #[Test]
    public function includeToolDefinitions(): void
    {
        $processor = new SystemPromptInputProcessor(
            'This is a system prompt',
            new class implements ToolboxInterface {
                public function getTools(): array
                {
                    return [
                        new Tool(new ExecutionReference(ToolNoParams::class), 'tool_no_params', 'A tool without parameters', null),
                        new Tool(
                            new ExecutionReference(ToolRequiredParams::class, 'bar'),
                            'tool_required_params',
                            <<<DESCRIPTION
                                A tool with required parameters
                                or not
                                DESCRIPTION,
                            null
                        ),
                    ];
                }

                public function execute(ToolCall $toolCall): mixed
                {
                    return null;
                }
            }
        );

        $input = new Input(new GPT(), new MessageBag(Message::ofUser('This is a user message')), []);
        $processor->processInput($input);

        $messages = $input->messages->getMessages();
        self::assertCount(2, $messages);
        self::assertInstanceOf(SystemMessage::class, $messages[0]);
        self::assertInstanceOf(UserMessage::class, $messages[1]);
        self::assertSame(<<<PROMPT
            This is a system prompt

            # Available tools

            ## tool_no_params
            A tool without parameters

            ## tool_required_params
            A tool with required parameters
            or not
            PROMPT, $messages[0]->content);
    }

    #[Test]
    public function withStringableSystemPrompt(): void
    {
        $processor = new SystemPromptInputProcessor(
            new SystemPromptService(),
            new class implements ToolboxInterface {
                public function getTools(): array
                {
                    return [
                        new Tool(new ExecutionReference(ToolNoParams::class), 'tool_no_params', 'A tool without parameters', null),
                    ];
                }

                public function execute(ToolCall $toolCall): mixed
                {
                    return null;
                }
            }
        );

        $input = new Input(new GPT(), new MessageBag(Message::ofUser('This is a user message')), []);
        $processor->processInput($input);

        $messages = $input->messages->getMessages();
        self::assertCount(2, $messages);
        self::assertInstanceOf(SystemMessage::class, $messages[0]);
        self::assertInstanceOf(UserMessage::class, $messages[1]);
        self::assertSame(<<<PROMPT
            My dynamic system prompt.

            # Available tools

            ## tool_no_params
            A tool without parameters
            PROMPT, $messages[0]->content);
    }
}
