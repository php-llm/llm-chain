<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Anthropic\Model;

use PhpLlm\LlmChain\Anthropic\ClaudeRuntime;
use PhpLlm\LlmChain\Anthropic\Model\Claude\Version;
use PhpLlm\LlmChain\LanguageModel;
use PhpLlm\LlmChain\Message\MessageBag;

final class Claude implements LanguageModel
{
    public function __construct(
        private ClaudeRuntime $runtime,
        private Version $version = Version::SONNET_35,
        private float $temperature = 1.0,
        private int $maxTokens = 1000,
    ) {
    }

    public function call(MessageBag $messages, array $options = []): array
    {
        $system = $messages->getSystemMessage();

        return $this->runtime->request([
            'model' => $this->version->value,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
            'system' => $system->content,
            'messages' => $messages->withoutSystemMessage(),
        ]);
    }
}
