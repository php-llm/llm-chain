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
use PhpLlm\LlmChain\Model\LanguageModel;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;

final readonly class Chain implements ChainInterface
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
        private Platform $platform,
        private LanguageModel $llm,
        iterable $inputProcessor = [],
        iterable $outputProcessor = [],
    ) {
        $this->inputProcessor = $this->initializeProcessors($inputProcessor, InputProcessor::class);
        $this->outputProcessor = $this->initializeProcessors($outputProcessor, OutputProcessor::class);
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

        $response = $this->platform->request($this->llm, $messages, $options = $input->getOptions());

        if ($response instanceof AsyncResponse) {
            $response = $response->unwrap();
        }

        $output = new Output($this->llm, $response, $messages, $options);
        array_map(fn (OutputProcessor $processor) => $processor->processOutput($output), $this->outputProcessor);

        return $output->response;
    }

    /**
     * @param InputProcessor[]|OutputProcessor[] $processors
     *
     * @return InputProcessor[]|OutputProcessor[]
     */
    private function initializeProcessors(iterable $processors, string $interface): array
    {
        foreach ($processors as $processor) {
            if (!$processor instanceof $interface) {
                throw new InvalidArgumentException(sprintf('Processor %s must implement %s interface.', $processor::class, $interface));
            }

            if ($processor instanceof ChainAwareProcessor) {
                $processor->setChain($this);
            }
        }

        return $processors instanceof \Traversable ? iterator_to_array($processors) : $processors;
    }
}
