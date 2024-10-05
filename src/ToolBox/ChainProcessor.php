<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

use PhpLlm\LlmChain\Chain\ChainAwareProcessor;
use PhpLlm\LlmChain\Chain\ChainAwareTrait;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Message\AssistantMessage;
use PhpLlm\LlmChain\Message\ToolCallMessage;
use PhpLlm\LlmChain\Response\ToolCallResponse;

final class ChainProcessor implements InputProcessor, OutputProcessor, ChainAwareProcessor
{
    use ChainAwareTrait;

    public function __construct(
        private ToolBoxInterface $toolBox,
    ) {
    }

    public function processInput(Input $input): void
    {
        if (!$input->llm->supportsToolCalling()) {
            throw MissingModelSupport::forToolCalling($input->llm::class);
        }

        $options = $input->getOptions();
        $options['tools'] = $this->toolBox->getMap();
        $input->setOptions($options);
    }

    public function processOutput(Output $output): void
    {
        $messages = clone $output->messages;

        while ($output->response instanceof ToolCallResponse) {
            $toolCalls = $output->response->getContent();
            $messages[] = new AssistantMessage(toolCalls: $toolCalls);

            foreach ($toolCalls as $toolCall) {
                $result = $this->toolBox->execute($toolCall);
                $messages[] = new ToolCallMessage($toolCall, $result);
            }

            $output->response = $this->chain->call($messages, $output->options);
        }
    }
}
