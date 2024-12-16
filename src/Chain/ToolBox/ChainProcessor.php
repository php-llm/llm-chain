<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ChainAwareProcessor;
use PhpLlm\LlmChain\Chain\ChainAwareTrait;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;

final class ChainProcessor implements InputProcessor, OutputProcessor, ChainAwareProcessor
{
    use ChainAwareTrait;

    public function processInput(Input $input): void
    {
        if (!$input->llm->supportsToolCalling()) {
            throw MissingModelSupport::forToolCalling($input->llm::class);
        }

        $toolMap = $this->toolBox->getMap();
        if ([] === $toolMap) {
            return;
        }

        $options = $input->getOptions();
        $options['tools'] = $toolMap;
        $input->setOptions($options);
    }

    public function processOutput(Output $output): void
    {
        $messages = clone $output->messages;

        while ($output->response instanceof ToolCallResponse) {
            $toolCalls = $output->response->getContent();
            $messages[] = Message::ofAssistant(toolCalls: $toolCalls);

            foreach ($toolCalls as $toolCall) {
                $result = $this->toolBox->execute($toolCall);
                $messages[] = Message::ofToolCall($toolCall, $result);
            }

            $output->response = $this->chain->process($messages, $output->options, $this);
        }
    }
}
