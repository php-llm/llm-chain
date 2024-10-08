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
use PhpLlm\LlmChain\Message\Message;
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

        $options = $output->options;

        while ($output->response instanceof ToolCallResponse) {
            $toolCalls = $output->response->getContent();
            $messages[] = Message::ofAssistant(toolCalls: $toolCalls);

            foreach ($toolCalls as $toolCall) {
                foreach ($this->toolBox->getMap() as $metadata) {
                    if ($metadata->name === $toolCall->name
                        && null !== $metadata->responseFormat
                    ) {
                        $options = array_merge($options, [
                            'response_format' => [
                                'type' => 'json_schema',
                                'json_schema' => [
                                    'schema' => $metadata->responseFormat,
                                    'name' => $metadata->name,
                                ],
                            ],
                        ]);
                        break;
                    }
                }

                $result = $this->toolBox->execute($toolCall);
                $messages[] = Message::ofToolCall($toolCall, $result);
            }

            $output->response = $this->chain->call($messages, $options);
        }
    }
}
