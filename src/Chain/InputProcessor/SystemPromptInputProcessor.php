<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\InputProcessor;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\ToolBox\Metadata;
use PhpLlm\LlmChain\Chain\ToolBox\ToolBoxInterface;
use PhpLlm\LlmChain\Model\Message\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class SystemPromptInputProcessor implements InputProcessor
{
    /**
     * @param \Stringable|string    $systemPrompt the system prompt to prepend to the input messages
     * @param ToolBoxInterface|null $toolBox      the tool box to be used to append the tool definitions to the system prompt
     */
    public function __construct(
        private \Stringable|string $systemPrompt,
        private ?ToolBoxInterface $toolBox = null,
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

        if ($this->toolBox instanceof ToolBoxInterface
            && [] !== $this->toolBox->getMap()
        ) {
            $this->logger->debug('Append tool definitions to system prompt.');

            $tools = implode(PHP_EOL.PHP_EOL, array_map(
                fn (Metadata $tool) => <<<TOOL
                    ## {$tool->name}
                    {$tool->description}
                    TOOL,
                $this->toolBox->getMap()
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
