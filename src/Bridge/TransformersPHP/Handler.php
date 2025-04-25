<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\TransformersPHP;

use Codewithkyrian\Transformers\Pipelines\Task;
use PhpLlm\LlmChain\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Model\Model as BaseModel;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\StructuredResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Platform\ModelClient;
use PhpLlm\LlmChain\Platform\ResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

final readonly class Handler implements ModelClient, ResponseConverter
{
    public function supports(BaseModel $model, object|array|string $input): bool
    {
        return $model instanceof Model;
    }

    public function request(BaseModel $model, object|array|string $input, array $options = []): ResponseInterface
    {
        if (!isset($options['task'])) {
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

        return new PipelineResponse($pipeline, $input);
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        if (!$response instanceof PipelineResponse) {
            throw new InvalidArgumentException('The response is not a valid TransformersPHP response.');
        }

        $task = $options['task'];
        $data = $response->toArray();

        return match ($task) {
            Task::Text2TextGeneration => new TextResponse($data[0]['generated_text']),
            default => new StructuredResponse($response->toArray()),
        };
    }
}
