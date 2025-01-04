<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Chain\InputProcessor;

use PhpLlm\LlmChain\Chain\Input;
use PhpLlm\LlmChain\Chain\InputProcessor;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Model\LanguageModel;

final class LlmOverrideInputProcessor implements InputProcessor
{
    public function processInput(Input $input): void
    {
        $options = $input->getOptions();

        if (!array_key_exists('llm', $options)) {
            return;
        }

        if (!$options['llm'] instanceof LanguageModel) {
            throw new InvalidArgumentException(sprintf('Option "llm" must be an instance of %s.', LanguageModel::class));
        }

        $input->llm = $options['llm'];
    }
}
