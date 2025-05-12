<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\InputProcessor;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessorInterface;
use PhpLlm\LlmChain\Chain\Toolbox\ToolboxInterface;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Tool\Tool;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class SystemPromptInputProcessor implements InputProcessorInterface
{
    /**
     * @param \Stringable|string    $systemPrompt the system prompt to prepend to the input messages
     * @param ToolboxInterface|null $toolbox      the tool box to be used to append the tool definitions to the system prompt
     */
    public function __construct(
        private \Stringable|string $systemPrompt,
        private ?ToolboxInterface $toolbox = null,
        private LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public function processInput(Input $input): void
    {
        $messages = $input->messages;

        if (null !== $messages->getSystemMessage()) {
            $this->logger->debug('Skipping system prompt injection since MessageBag already contains a system message.');

            return;
        }

        $message = (string) $this->systemPrompt;

        if ($this->toolbox instanceof ToolboxInterface
            && [] !== $this->toolbox->getTools()
        ) {
            $this->logger->debug('Append tool definitions to system prompt.');

            $tools = implode(\PHP_EOL.\PHP_EOL, array_map(
                fn (Tool $tool) => <<<TOOL
                    ## {$tool->name}
                    {$tool->description}
                    TOOL,
                $this->toolbox->getTools()
            ));

            $message = <<<PROMPT
                {$this->systemPrompt}

                # Available tools

                {$tools}
                PROMPT;
        }

        $input->messages = $messages->prepend(Message::forSystem($message));
    }
}
