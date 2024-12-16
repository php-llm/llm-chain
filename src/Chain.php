<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain;

use PhpLlm\LlmChain\Chain\ChainAwareProcessor;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Chain\Output;
use PhpLlm\LlmChain\Chain\OutputProcessor;
use PhpLlm\LlmChain\Exception\MissingModelSupport;
use PhpLlm\LlmChain\Model\LanguageModel;
use PhpLlm\LlmChain\Model\Message\MessageBag;
use PhpLlm\LlmChain\Model\Response\AsyncResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;

final readonly class Chain implements ChainInterface
{
    public function __construct(
        private PlatformInterface $platform,
        private LanguageModel $llm,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function process(MessageBag $messages, array $options = [], ?ChainAwareProcessor $chainProcessor = null): ResponseInterface
    {
        $input = new Input($this->llm, $messages, $options);

        if ($chainProcessor) {
            array_map(fn (InputProcessor $processor) => $processor->processInput($input), $chainProcessor->getInputProcessors());
        }

        if ($messages->containsImage() && !$this->llm->supportsImageInput()) {
            throw MissingModelSupport::forImageInput($this->llm::class);
        }

        $response = $this->platform->request($this->llm, $messages, $options = $input->getOptions());

        if ($response instanceof AsyncResponse) {
            $response = $response->unwrap();
        }

        $output = new Output($this->llm, $response, $messages, $options);

        if ($chainProcessor) {
            array_map(fn (OutputProcessor $processor) => $processor->processOutput($output), $chainProcessor->getOutputProcessors());
        }

        return $output->response;
    }
}
