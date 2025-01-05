<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\ToolBox;

use PhpLlm\LlmChain\Chain\ChainAwareProcessor;
use PhpLlm\LlmChain\Chain\ChainAwareTrait;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Chain\ToolBox\Event\ToolCallsExecuted;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ChainProcessor implements InputProcessor, OutputProcessor, ChainAwareProcessor
{
    use ChainAwareTrait;

    public function __construct(
        private readonly ToolBoxInterface $toolBox,
        private ToolResultConverter $resultConverter = new ToolResultConverter(),
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

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
            $messages->add(Message::ofAssistant(toolCalls: $toolCalls));

            $results = [];
            foreach ($toolCalls as $toolCall) {
                $result = $this->toolBox->execute($toolCall);
                $results[] = new ToolCallResult($toolCall, $result);
                $messages->add(Message::ofToolCall($toolCall, $this->resultConverter->convert($result)));
            }

            $event = new ToolCallsExecuted(...$results);
            $this->eventDispatcher?->dispatch($event);

            $output->response = $event->hasResponse() ? $event->response : $this->chain->call($messages, $output->options);
        }
    }
}
