<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\InputProcessor;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Model\Model;

final class ModelOverrideInputProcessor implements InputProcessor
{
    public function processInput(Input $input): void
    {
        $options = $input->getOptions();

        if (!array_key_exists('model', $options)) {
            return;
        }

        if (!$options['model'] instanceof Model) {
            throw new InvalidArgumentException(sprintf('Option "model" must be an instance of %s.', Model::class));
        }

        $input->model = $options['model'];
    }
}
