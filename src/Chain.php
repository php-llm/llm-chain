<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Chain\ChainAwareProcessor;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Response\ResponseInterface;

final readonly class Chain
{
    /**
     * @var InputProcessor[]
     */
    private array $inputProcessor;

    /**
     * @var OutputProcessor[]
     */
    private array $outputProcessor;

    /**
     * @param InputProcessor[]  $inputProcessor
     * @param OutputProcessor[] $outputProcessor
     */
    public function __construct(
        private LanguageModel $llm,
        iterable $inputProcessor = [],
        iterable $outputProcessor = [],
    ) {
        $processors = [];
        foreach ($inputProcessor as $input) {
            if (!$input instanceof InputProcessor) {
                throw new InvalidArgumentException('Input processor must implement InputProcessor interface.');
            }

            if ($input instanceof ChainAwareProcessor) {
                $input->setChain($this);
            }

            $processors[] = $input;
        }
        $this->inputProcessor = $processors;

        $processors = [];
        foreach ($outputProcessor as $output) {
            if (!$output instanceof OutputProcessor) {
                throw new InvalidArgumentException('Output processor must implement OutputProcessor interface.');
            }

            if ($output instanceof ChainAwareProcessor) {
                $output->setChain($this);
            }

            $processors[] = $output;
        }
        $this->outputProcessor = $processors;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBag $messages, array $options = []): ResponseInterface
    {
        $input = new Input($this->llm, $messages, $options);
        array_map(fn (InputProcessor $processor) => $processor->processInput($input), $this->inputProcessor);

        if ($messages->containsImage() && !$this->llm->supportsImageInput()) {
            throw MissingModelSupport::forImageInput($this->llm::class);
        }

        $response = $this->llm->call($messages, $input->getOptions());

        $output = new Output($this->llm, $response, $messages, $options);
        array_map(fn (OutputProcessor $processor) => $processor->processOutput($output), $this->outputProcessor);

        return $output->response;
    }
}
