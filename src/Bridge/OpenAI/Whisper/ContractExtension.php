<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\OpenAI\Whisper;

use PhpLlm\LlmChain\Bridge\OpenAI\Whisper;
use PhpLlm\LlmChain\Model\Message\Content\Audio;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Platform\Contract\Extension;

final class ContractExtension implements Extension
{
    public function supports(Model $model): bool
    {
        return $model instanceof Whisper;
    }

    public function registerTypes(): array
    {
        return [
            Audio::class => 'handleAudioInput',
        ];
    }

    public function handleAudioInput(Audio $audio): array
    {
        return [
            'file' => $audio->asResource(),
        ];
    }
}
