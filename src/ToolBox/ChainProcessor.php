<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Message\Message;

final readonly class ChainProcessor implements InputProcessor, OutputProcessor
{
    public function __construct(
        private ToolBox $toolBox,
    ) {
    }

    public function processInput(Input $input): void
    {
        if (!$input->llm->supportsToolCalling()) {
            throw MissingModelSupport::forToolCalling($input->llm::class);
        }

        $options['tools'] = $this->toolBox->getMap();
        $input->setOptions($options);
    }

    public function processOutput(Output $output): mixed
    {
        $response = $output->response;
        $messages = clone $output->messages;

        while ($response->hasToolCalls()) {
            $messages[] = Message::ofAssistant(toolCalls: $response->getToolCalls());

            foreach ($response->getToolCalls() as $toolCall) {
                $result = $this->toolBox->execute($toolCall);
                $messages[] = Message::ofToolCall($toolCall, $result);
            }

            $response = $output->llm->call($messages, $output->options);
        }

        return $response->getContent();
    }
}
