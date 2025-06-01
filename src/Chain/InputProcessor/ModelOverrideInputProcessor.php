<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\InputProcessor;

use PhpLlm\LlmChain\Chain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessorInterface;
use PhpLlm\LlmChain\Platform\Model;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class ModelOverrideInputProcessor implements InputProcessorInterface
{
    public function processInput(Input $input): void
    {
        $options = $input->getOptions();

        if (!\array_key_exists('model', $options)) {
            return;
        }

        if (!$options['model'] instanceof Model) {
            throw new InvalidArgumentException(\sprintf('Option "model" must be an instance of %s.', Model::class));
        }

        $input->model = $options['model'];
    }
}
