<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\TransformersPHP;

use Codewithkyrian\Transformers\Pipelines\Task;
use PhpLlm\LlmChain\Platform\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\PlatformInterface;
use PhpLlm\LlmChain\Platform\Response\ObjectResponse;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface;
use PhpLlm\LlmChain\Platform\Response\TextResponse;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
final class Platform implements PlatformInterface
{
    public function request(Model $model, object|array|string $input, array $options = []): ResponseInterface
    {
        if (null === $task = $options['task'] ?? null) {
            throw new InvalidArgumentException('The task option is required.');
        }

        $pipeline = pipeline(
            $options['task'],
            $model->getName(),
            $options['quantized'] ?? true,
            $options['config'] ?? null,
            $options['cacheDir'] ?? null,
            $options['revision'] ?? 'main',
            $options['modelFilename'] ?? null,
        );

        $data = $pipeline($input);

        return match ($task) {
            Task::Text2TextGeneration => new TextResponse($data[0]['generated_text']),
            default => new ObjectResponse($data),
        };
    }
}
