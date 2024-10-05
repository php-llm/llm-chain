<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\StructuredOutput;

use PhpLlm\LlmChain\Event\InputEvent;
use PhpLlm\LlmChain\Event\OutputEvent;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Response\StructuredResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class ChainSubscriber implements EventSubscriberInterface
{
    private string $outputStructure;

    public function __construct(
        private readonly ResponseFormatFactoryInterface $responseFormatFactory,
        private readonly SerializerInterface $serializer,
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
        $options = $input->getOptions();

        if (!isset($options['output_structure'])) {
            return;
        }

        if (!$input->llm->supportsStructuredOutput()) {
            throw MissingModelSupport::forStructuredOutput($input->llm::class);
        }

        if (true === ($options['stream'] ?? false)) {
            throw new InvalidArgumentException('Streamed responses are not supported for structured output');
        }

        $options['response_format'] = $this->responseFormatFactory->create($options['output_structure']);

        $this->outputStructure = $options['output_structure'];
        unset($options['output_structure']);

        $input->setOptions($options);
    }

    public function processOutput(OutputEvent $output): void
    {
        $options = $output->options;

        if ($output->response instanceof StructuredResponse) {
            return;
        }

        if (!isset($options['response_format']) && !isset($options['output_structure'])) {
            return;
        }

        if (isset($options['response_format']) && !isset($options['output_structure'])) {
            $output->response = new StructuredResponse(json_decode($output->response->getContent(), true));

            return;
        }

        $output->response = new StructuredResponse(
            $this->serializer->deserialize($output->response->getContent(), $this->outputStructure, 'json')
        );
    }
}
