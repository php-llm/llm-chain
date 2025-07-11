<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Google\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\Content\File;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Model;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class UserMessageNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return UserMessage::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Gemini;
    }

    /**
     * @param UserMessage $data
     *
     * @return list<array{inline_data?: array{mime_type: string, data: string}}>
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $parts = [];
        foreach ($data->content as $content) {
            if ($content instanceof Text) {
                $parts[] = ['text' => $content->text];
            }
            if ($content instanceof File) {
                $parts[] = ['inline_data' => [
                    'mime_type' => $content->getFormat(),
                    'data' => $content->asBase64(),
                ]];
            }
        }

        return $parts;
    }
}
