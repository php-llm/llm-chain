<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Event\InputEvent;
use PhpLlm\LlmChain\Event\OutputEvent;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\ResponseInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class Chain
{
    public function __construct(
        private LanguageModel $llm,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBag $messages, array $options = []): ResponseInterface
    {
        $this->eventDispatcher->dispatch($input = new InputEvent($this->llm, $messages, $options));

        $response = $this->llm->call($messages, $input->getOptions());

        $this->eventDispatcher->dispatch($output = new OutputEvent($this->llm, $response, $messages, $options));

        return $output->response;
    }
}
