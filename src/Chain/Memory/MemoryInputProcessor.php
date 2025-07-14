<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Memory;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessorInterface;
use PhpLlm\LlmChain\Platform\Message\Message;

/**
 * @author Denis Zunke <denis.zunke@gmail.com>
 */
final readonly class MemoryInputProcessor implements InputProcessorInterface
{
    private const MEMORY_PROMPT_MESSAGE = <<<MARKDOWN
        # Conversation Memory
        This is the memory I have found for this conversation. The memory has more weight to answer user input,
        so try to answer utilizing the memory as much as possible. Your answer must be changed to fit the given
        memory. If the memory is irrelevant, ignore it. Do not reply to the this section of the prompt and do not
        reference it as this is just for your reference.
        MARKDOWN;

    /**
     * @var MemoryProviderInterface[]
     */
    private array $memoryProviders;

    public function __construct(
        MemoryProviderInterface ...$memoryProviders,
    ) {
        $this->memoryProviders = $memoryProviders;
    }

    public function processInput(Input $input): void
    {
        $options = $input->getOptions();
        $useMemory = $options['use_memory'] ?? true;
        unset($options['use_memory']);
        $input->setOptions($options);

        if (false === $useMemory || 0 === \count($this->memoryProviders)) {
            return;
        }

        $memory = '';
        foreach ($this->memoryProviders as $provider) {
            $memoryMessages = $provider->loadMemory($input);

            if (0 === \count($memoryMessages)) {
                continue;
            }

            $memory .= \PHP_EOL.\PHP_EOL;
            $memory .= implode(
                \PHP_EOL,
                array_map(static fn (Memory $memory): string => $memory->content, $memoryMessages),
            );
        }

        if ('' === $memory) {
            return;
        }

        $systemMessage = $input->messages->getSystemMessage()->content ?? '';
        if ('' !== $systemMessage) {
            $systemMessage .= \PHP_EOL.\PHP_EOL;
        }

        $messages = $input->messages
            ->withoutSystemMessage()
            ->prepend(Message::forSystem($systemMessage.self::MEMORY_PROMPT_MESSAGE.$memory));

        $input->messages = $messages;
    }
}
