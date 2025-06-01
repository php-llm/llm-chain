<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Bedrock\Nova\Nova;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Message\Content\Image;
use PhpLlm\LlmChain\Platform\Message\Content\Text;
use PhpLlm\LlmChain\Platform\Message\UserMessage;
use PhpLlm\LlmChain\Platform\Model;

use function Symfony\Component\String\u;

final class UserMessageNormalizer extends ModelContractNormalizer
{
    protected function supportedDataClass(): string
    {
        return UserMessage::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Nova;
    }

    /**
     * @param UserMessage $data
     *
     * @return array{
     *     role: 'user',
     *     content: array<array{
     *         text?: string,
     *         image?: array{
     *             format: string,
     *             source: array{bytes: string}
     *         }
     *     }>
     * }
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $array = ['role' => $data->getRole()->value];

        foreach ($data->content as $value) {
            $contentPart = [];
            if ($value instanceof Text) {
                $contentPart['text'] = $value->text;
            } elseif ($value instanceof Image) {
                $contentPart['image']['format'] = u($value->getFormat())->replace('image/', '')->replace('jpg', 'jpeg')->toString();
                $contentPart['image']['source']['bytes'] = $value->asBase64();
            } else {
                throw new RuntimeException('Unsupported message type.');
            }
            $array['content'][] = $contentPart;
        }

        return $array;
    }
}
