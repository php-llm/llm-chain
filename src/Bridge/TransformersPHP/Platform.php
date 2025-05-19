<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\TransformersPHP;

use Codewithkyrian\Transformers\Pipelines\Task;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Model\Model;
use PhpLlm\LlmChain\Model\Response\ResponseInterface;
use PhpLlm\LlmChain\Model\Response\StructuredResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\PlatformInterface;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

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
            default => new StructuredResponse($data),
        };
    }
}
