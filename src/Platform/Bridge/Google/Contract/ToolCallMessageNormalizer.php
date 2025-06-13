<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Google\Contract;

use PhpLlm\LlmChain\Platform\Bridge\Google\Gemini;
use PhpLlm\LlmChain\Platform\Contract\Normalizer\ModelContractNormalizer;
use PhpLlm\LlmChain\Platform\Message\ToolCallMessage;
use PhpLlm\LlmChain\Platform\Model;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * @author Valtteri R <valtzu@gmail.com>
 */
final class ToolCallMessageNormalizer extends ModelContractNormalizer implements NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    protected function supportedDataClass(): string
    {
        return ToolCallMessage::class;
    }

    protected function supportsModel(Model $model): bool
    {
        return $model instanceof Gemini;
    }

    /**
     * @param ToolCallMessage $data
     *
     * @return array{
     *      functionResponse: array{
     *          id: string,
     *          name: string,
     *          response: array<int|string, mixed>
     *      }
     *  }[]
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $responseContent = json_validate($data->content) ? json_decode($data->content, true) : $data->content;

        return [[
            'functionResponse' => array_filter([
                'id' => $data->toolCall->id,
                'name' => $data->toolCall->name,
                'response' => \is_array($responseContent) ? $responseContent : [
                    'rawResponse' => $responseContent, // Gemini expects the response to be an object, but not everyone uses objects as their responses.
                ],
            ]),
        ]];
    }
}
