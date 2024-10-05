<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\ToolBox;

use PhpLlm\LlmChain\Chain;
use PhpLlm\LlmChain\Event\InputEvent;
use PhpLlm\LlmChain\Event\OutputEvent;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Message\Message;
use PhpLlm\LlmChain\Response\ToolCallResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class ChainSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ToolBoxInterface $toolBox,
        private Chain $chain,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InputEvent::class => 'processInput',
            OutputEvent::class => 'processOutput',
        ];
    }

    public function processInput(InputEvent $input): void
    {
        if (!$input->llm->supportsToolCalling()) {
            throw MissingModelSupport::forToolCalling($input->llm::class);
        }

        $options = $input->getOptions();
        $options['tools'] = $this->toolBox->getMap();
        $input->setOptions($options);
    }

    public function processOutput(OutputEvent $output): void
    {
        $messages = clone $output->messages;

        while ($output->response instanceof ToolCallResponse) {
            $toolCalls = $output->response->getContent();
            $messages[] = Message::ofAssistant(toolCalls: $toolCalls);

            foreach ($toolCalls as $toolCall) {
                $result = $this->toolBox->execute($toolCall);
                $messages[] = Message::ofToolCall($toolCall, $result);
            }

            $output->response = $this->chain->call($messages, $output->options);
        }
    }
}
