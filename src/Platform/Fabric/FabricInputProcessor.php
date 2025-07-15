<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Fabric;

use PhpLlm\FabricPattern\Pattern;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessorInterface;
use PhpLlm\LlmChain\Platform\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Platform\Exception\LogicException;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Message\SystemMessage;

/**
 * Requires the "php-llm/fabric-pattern" package to be installed.
 */
final readonly class FabricInputProcessor implements InputProcessorInterface
{
    public function processInput(Input $input): void
    {
        $options = $input->getOptions();

        if (!\array_key_exists('fabric_pattern', $options)) {
            return;
        }

        $pattern = $options['fabric_pattern'];
        if (!\is_string($pattern)) {
            throw new InvalidArgumentException('The "fabric_pattern" option must be a string');
        }

        if (null !== $input->messages->getSystemMessage()) {
            throw new LogicException('Cannot add Fabric pattern: MessageBag already contains a system message');
        }

        if (!class_exists(Pattern::class)) {
            throw new RuntimeException('Fabric patterns not found. Please install the "php-llm/fabric-pattern" package: composer require php-llm/fabric-pattern');
        }

        $content = (new Pattern())->load($pattern);
        $systemMessage = new SystemMessage($content);

        $input->messages = $input->messages->prepend($systemMessage);

        unset($options['fabric_pattern']);
        $input->setOptions($options);
    }
}
