<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Model\Language;

use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;
use PhpLlm\LlmChain\Platform\Ollama;
use PhpLlm\LlmChain\Platform\Replicate;
use PhpLlm\LlmChain\Response\TextResponse;

final readonly class Llama implements LanguageModel
{
    public function __construct(
        private Replicate|Ollama $platform,
    ) {
    }

    public function call(MessageBag $messages, array $options = []): TextResponse
    {
        $systemMessage = $messages->getSystemMessage();
        $endpoint = $this->platform instanceof Replicate ? 'predictions' : 'chat';

        $response = $this->platform->request('meta/meta-llama-3.1-405b-instruct', $endpoint, [
            'system' => $systemMessage?->content,
            'prompt' => $messages->withoutSystemMessage()->getIterator()->current()->content[0]->text, // @phpstan-ignore-line TODO: Multiple messages
        ]);

        return new TextResponse(implode('', $response['output']));
    }

    public function supportsToolCalling(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsImageInput(): bool
    {
        return false; // it does, but implementation here is still open.
    }

    public function supportsStructuredOutput(): bool
    {
        return false;
    }
}
