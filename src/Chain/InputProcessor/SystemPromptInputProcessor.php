<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\InputProcessor;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Model\Message\Message;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final readonly class SystemPromptInputProcessor implements InputProcessor
{
    public function __construct(
        private string $systemPrompt,
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

        $input->messages = $messages->prepend(Message::forSystem($this->systemPrompt));
    }
}
