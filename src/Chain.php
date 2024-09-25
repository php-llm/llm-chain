<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Message\MessageBag;

final readonly class Chain
{
    /**
     * @param InputProcessor[]  $inputProcessor
     * @param OutputProcessor[] $outputProcessor
     */
    public function __construct(
        private LanguageModel $llm,
        private array $inputProcessor = [],
        private array $outputProcessor = [],
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function call(MessageBag $messages, array $options = []): string|object
    {
        $input = new Input($this->llm, $messages, $options);
        array_map(fn (InputProcessor $processor) => $processor->processInput($input), $this->inputProcessor);

        if ($messages->containsImage() && !$this->llm->supportsImageInput()) {
            throw MissingModelSupport::forImageInput($this->llm::class);
        }

        $response = $this->llm->call($messages, $input->getOptions());

        $output = new Output($this->llm, $response, $messages, $options);
        foreach ($this->outputProcessor as $outputProcessor) {
            $result = $outputProcessor->processOutput($output);

            if (null !== $result) {
                return $result;
            }
        }

        return $response->getContent();
    }
}
