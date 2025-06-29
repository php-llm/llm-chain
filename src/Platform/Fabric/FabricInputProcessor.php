<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Fabric;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessorInterface;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;

/**
 * Input processor for Fabric patterns.
 *
 * Requires the "php-llm/fabric-pattern" package to be installed.
 *
 * This processor allows adding Fabric patterns through options:
 * - fabric_pattern: string - The pattern name to load
 */
final readonly class FabricInputProcessor implements InputProcessorInterface
{
    private FabricRepository $repository;

    public function __construct(?FabricRepository $repository = null)
    {
        $this->repository = $repository ?? new FabricRepository();
    }

    public function processInput(Input $input): void
    {
        $options = $input->getOptions();

        if (!\array_key_exists('fabric_pattern', $options)) {
            return;
        }

        $pattern = $options['fabric_pattern'];
        if (!\is_string($pattern)) {
            throw new \InvalidArgumentException('The "fabric_pattern" option must be a string');
        }

        // Load the pattern and prepend as system message
        $fabricPrompt = $this->repository->load($pattern);
        $systemMessage = new SystemMessage($fabricPrompt->getContent());

        // Prepend the system message
        $input->messages = $input->messages->prepend($systemMessage);

        // Remove the fabric option from the chain options
        unset($options['fabric_pattern']);
        $input->setOptions($options);
    }
}
