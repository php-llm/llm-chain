<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\Toolbox;

use PhpLlm\LlmChain\Chain\ChainAwareProcessor;
use PhpLlm\LlmChain\Chain\ChainAwareTrait;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Event\ToolCallsExecuted;
use PhpLlm\LlmChain\Chain\Toolbox\StreamResponse as ToolboxStreamResponse;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Model\Capability;
use PhpLlm\LlmChain\Model\Message\AssistantMessage;
use PhpLlm\LlmChain\Model\Message\Message;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PhpLlm\LlmChain\Model\Response\StreamResponse as GenericStreamResponse;
use PhpLlm\LlmChain\Model\Response\ToolCallResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ChainProcessor implements InputProcessor, OutputProcessor, ChainAwareProcessor
{
    use ChainAwareTrait;

    public function __construct(
        private readonly ToolboxInterface $toolbox,
        private readonly ToolResultConverter $resultConverter = new ToolResultConverter(),
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
    ) {
    }

    public function processInput(Input $input): void
    {
        if (!$input->model->supports(Capability::TOOL_CALLING)) {
            throw MissingModelSupport::forToolCalling($input->model::class);
        }

        $toolMap = $this->toolbox->getMap();
        if ([] === $toolMap) {
            return;
        }

        $options = $input->getOptions();
        // only filter tool map if list of strings is provided as option
        if (isset($options['tools']) && $this->isFlatStringArray($options['tools'])) {
            $toolMap = array_values(array_filter($toolMap, fn (Metadata $tool) => in_array($tool->name, $options['tools'], true)));
        }

        $options['tools'] = $toolMap;
        $input->setOptions($options);
    }

    public function processOutput(Output $output): void
    {
        if ($output->response instanceof GenericStreamResponse) {
            $output->response = new ToolboxStreamResponse(
                $output->response->getContent(),
                $this->handleToolCallsCallback($output),
            );

            return;
        }

        if (!$output->response instanceof ToolCallResponse) {
            return;
        }

        $output->response = $this->handleToolCallsCallback($output)($output->response);
    }

    /**
     * @param array<mixed> $tools
     */
    private function isFlatStringArray(array $tools): bool
    {
        return array_reduce($tools, fn (bool $carry, mixed $item) => $carry && is_string($item), true);
    }

    private function handleToolCallsCallback(Output $output): \Closure
    {
        return function (ToolCallResponse $response, ?AssistantMessage $streamedAssistantResponse = null) use ($output): ResponseInterface {
            $messages = clone $output->messages;

            if (null !== $streamedAssistantResponse && '' !== $streamedAssistantResponse->content) {
                $messages->add($streamedAssistantResponse);
            }

            do {
                $toolCalls = $response->getContent();
                $messages->add(Message::ofAssistant(toolCalls: $toolCalls));

                $results = [];
                foreach ($toolCalls as $toolCall) {
                    $result = $this->toolbox->execute($toolCall);
                    $results[] = new ToolCallResult($toolCall, $result);
                    $messages->add(Message::ofToolCall($toolCall, $this->resultConverter->convert($result)));
                }

                $event = new ToolCallsExecuted(...$results);
                $this->eventDispatcher?->dispatch($event);

                $response = $event->hasResponse() ? $event->response : $this->chain->call($messages, $output->options);
            } while ($response instanceof ToolCallResponse);

            return $response;
        };
    }
}
