<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\Google\Contract;

use PhpLlm\LlmChain\Model\Message\Content\Image;
use PhpLlm\LlmChain\Model\Message\Content\Text;
use PhpLlm\LlmChain\Model\Message\UserMessage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class UserMessageNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UserMessage;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            UserMessage::class => true,
        ];
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
            if ($content instanceof Image) {
                $parts[] = ['inline_data' => [
                    'mime_type' => $content->getFormat(),
                    'data' => $content->asBase64(),
                ]];
            }
        }

        return $parts;
    }
}
