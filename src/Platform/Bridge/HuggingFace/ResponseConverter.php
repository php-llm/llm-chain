<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace;

use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output\ClassificationResult;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output\FillMaskResult;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output\ImageSegmentationResult;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output\ObjectDetectionResult;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output\QuestionAnsweringResult;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output\SentenceSimilarityResult;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output\TableQuestionAnsweringResult;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output\TokenClassificationResult;
use PhpLlm\LlmChain\Platform\Bridge\HuggingFace\Output\ZeroShotClassificationResult;
use PhpLlm\LlmChain\Platform\Exception\InvalidArgumentException;
use PhpLlm\LlmChain\Platform\Exception\RuntimeException;
use PhpLlm\LlmChain\Platform\Model;
use PhpLlm\LlmChain\Platform\Response\BinaryResponse;
use PhpLlm\LlmChain\Platform\Response\ObjectResponse;
use PhpLlm\LlmChain\Platform\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Platform\Response\TextResponse;
use PhpLlm\LlmChain\Platform\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ResponseConverterInterface as PlatformResponseConverter;
use PhpLlm\LlmChain\Platform\Vector\Vector;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class ResponseConverter implements PlatformResponseConverter
{
    public function supports(Model $model): bool
    {
        return true;
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        if (503 === $response->getStatusCode()) {
            return throw new RuntimeException('Service unavailable.');
        }

        if (404 === $response->getStatusCode()) {
            return throw new InvalidArgumentException('Model, provider or task not found (404).');
        }

        $headers = $response->getHeaders(false);
        $contentType = $headers['content-type'][0] ?? null;
        $content = 'application/json' === $contentType ? $response->toArray(false) : $response->getContent(false);

        if (str_starts_with((string) $response->getStatusCode(), '4')) {
            $message = \is_string($content) ? $content :
                (\is_array($content['error']) ? $content['error'][0] : $content['error']);

            throw new InvalidArgumentException(\sprintf('API Client Error (%d): %s', $response->getStatusCode(), $message));
        }

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException('Unhandled response code: '.$response->getStatusCode());
        }

        $task = $options['task'] ?? null;

        return match ($task) {
            Task::AUDIO_CLASSIFICATION, Task::IMAGE_CLASSIFICATION => new ObjectResponse(
                ClassificationResult::fromArray($content)
            ),
            Task::AUTOMATIC_SPEECH_RECOGNITION => new TextResponse($content['text'] ?? ''),
            Task::CHAT_COMPLETION => new TextResponse($content['choices'][0]['message']['content'] ?? ''),
            Task::FEATURE_EXTRACTION => new VectorResponse(new Vector($content)),
            Task::TEXT_CLASSIFICATION => new ObjectResponse(ClassificationResult::fromArray(reset($content) ?? [])),
            Task::FILL_MASK => new ObjectResponse(FillMaskResult::fromArray($content)),
            Task::IMAGE_SEGMENTATION => new ObjectResponse(ImageSegmentationResult::fromArray($content)),
            Task::IMAGE_TO_TEXT, Task::TEXT_GENERATION => new TextResponse($content[0]['generated_text'] ?? ''),
            Task::TEXT_TO_IMAGE => new BinaryResponse($content, $contentType),
            Task::OBJECT_DETECTION => new ObjectResponse(ObjectDetectionResult::fromArray($content)),
            Task::QUESTION_ANSWERING => new ObjectResponse(QuestionAnsweringResult::fromArray($content)),
            Task::SENTENCE_SIMILARITY => new ObjectResponse(SentenceSimilarityResult::fromArray($content)),
            Task::SUMMARIZATION => new TextResponse($content[0]['summary_text']),
            Task::TABLE_QUESTION_ANSWERING => new ObjectResponse(TableQuestionAnsweringResult::fromArray($content)),
            Task::TOKEN_CLASSIFICATION => new ObjectResponse(TokenClassificationResult::fromArray($content)),
            Task::TRANSLATION => new TextResponse($content[0]['translation_text'] ?? ''),
            Task::ZERO_SHOT_CLASSIFICATION => new ObjectResponse(ZeroShotClassificationResult::fromArray($content)),

            default => throw new RuntimeException(\sprintf('Unsupported task: %s', $task)),
        };
    }
}
