<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Bridge\HuggingFace;

use PhpLlm\LlmChain\Bridge\HuggingFace\Output\ClassificationResult;
use PhpLlm\LlmChain\Bridge\HuggingFace\Output\FillMaskResult;
use PhpLlm\LlmChain\Bridge\HuggingFace\Output\ImageSegmentationResult;
use PhpLlm\LlmChain\Bridge\HuggingFace\Output\ObjectDetectionResult;
use PhpLlm\LlmChain\Bridge\HuggingFace\Output\QuestionAnsweringResult;
use PhpLlm\LlmChain\Bridge\HuggingFace\Output\SentenceSimilarityResult;
use PhpLlm\LlmChain\Bridge\HuggingFace\Output\TableQuestionAnsweringResult;
use PhpLlm\LlmChain\Bridge\HuggingFace\Output\TokenClassificationResult;
use PhpLlm\LlmChain\Bridge\HuggingFace\Output\ZeroShotClassificationResult;
use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\Model\Model as BaseModel;
use PhpLlm\LlmChain\Model\Response\BinaryResponse;
use PhpLlm\LlmChain\Model\Response\ResponseInterface as LlmResponse;
use PhpLlm\LlmChain\Model\Response\StructuredResponse;
use PhpLlm\LlmChain\Model\Response\TextResponse;
use PhpLlm\LlmChain\Model\Response\VectorResponse;
use PhpLlm\LlmChain\Platform\ResponseConverter as PlatformResponseConverter;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class ResponseConverter implements PlatformResponseConverter
{
    public function supports(BaseModel $model, array|string|object $input): bool
    {
        return $model instanceof Model;
    }

    public function convert(ResponseInterface $response, array $options = []): LlmResponse
    {
        if (503 === $response->getStatusCode()) {
            return throw new \RuntimeException('Service unavailable.');
        }

        if (404 === $response->getStatusCode()) {
            return throw new \InvalidArgumentException('Model, provider or task not found (404).');
        }

        $headers = $response->getHeaders(false);
        $contentType = $headers['content-type'][0] ?? null;
        $content = 'application/json' === $contentType ? $response->toArray(false) : $response->getContent(false);

        if (str_starts_with((string) $response->getStatusCode(), '4')) {
            $message = is_string($content) ? $content :
                (is_array($content['error']) ? $content['error'][0] : $content['error']);

            throw new \InvalidArgumentException(sprintf('API Client Error (%d): %s', $response->getStatusCode(), $message));
        }

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Unhandled response code: '.$response->getStatusCode());
        }

        $task = $options['task'] ?? null;

        return match ($task) {
            Task::AUDIO_CLASSIFICATION, Task::IMAGE_CLASSIFICATION => new StructuredResponse(
                ClassificationResult::fromArray($content)
            ),
            Task::AUTOMATIC_SPEECH_RECOGNITION => new TextResponse($content['text'] ?? ''),
            Task::CHAT_COMPLETION => new TextResponse($content['choices'][0]['message']['content'] ?? ''),
            Task::FEATURE_EXTRACTION => new VectorResponse(new Vector($content)),
            Task::TEXT_CLASSIFICATION => new StructuredResponse(ClassificationResult::fromArray(reset($content) ?? [])),
            Task::FILL_MASK => new StructuredResponse(FillMaskResult::fromArray($content)),
            Task::IMAGE_SEGMENTATION => new StructuredResponse(ImageSegmentationResult::fromArray($content)),
            Task::IMAGE_TO_TEXT, Task::TEXT_GENERATION => new TextResponse($content[0]['generated_text'] ?? ''),
            Task::TEXT_TO_IMAGE => new BinaryResponse($content, $contentType),
            Task::OBJECT_DETECTION => new StructuredResponse(ObjectDetectionResult::fromArray($content)),
            Task::QUESTION_ANSWERING => new StructuredResponse(QuestionAnsweringResult::fromArray($content)),
            Task::SENTENCE_SIMILARITY => new StructuredResponse(SentenceSimilarityResult::fromArray($content)),
            Task::SUMMARIZATION => new TextResponse($content[0]['summary_text']),
            Task::TABLE_QUESTION_ANSWERING => new StructuredResponse(TableQuestionAnsweringResult::fromArray(dump($content))),
            Task::TOKEN_CLASSIFICATION => new StructuredResponse(TokenClassificationResult::fromArray($content)),
            Task::TRANSLATION => new TextResponse($content[0]['translation_text'] ?? ''),
            Task::ZERO_SHOT_CLASSIFICATION => new StructuredResponse(ZeroShotClassificationResult::fromArray($content)),

            default => throw new \RuntimeException(sprintf('Unsupported task: %s', $task)),
        };
    }
}
