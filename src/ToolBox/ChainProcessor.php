<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Response\ResponseInterface;
use PhpLlm\LlmChain\Response\ToolCallResponse;

final readonly class ChainProcessor implements InputProcessor, OutputProcessor
{
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

    public function processOutput(Output $output): ResponseInterface
    {
        $response = $output->response;
        $messages = clone $output->messages;

        while ($response instanceof ToolCallResponse) {
            $toolCalls = $response->getContent();
            $messages[] = Message::ofAssistant(toolCalls: $toolCalls);

            foreach ($toolCalls as $toolCall) {
                $result = $this->toolBox->execute($toolCall);
                $messages[] = Message::ofToolCall($toolCall, $result);
            }

            $response = $output->llm->call($messages, $output->options);
        }

        return $response;
    }
}
