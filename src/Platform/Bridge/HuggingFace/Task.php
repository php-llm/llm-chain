<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\HuggingFace;

/**
 * @author Christopher Hertel <mail@christopher-hertel.de>
 */
interface Task
{
    public const AUDIO_CLASSIFICATION = 'audio-classification';
    public const AUTOMATIC_SPEECH_RECOGNITION = 'automatic-speech-recognition';
    public const CHAT_COMPLETION = 'chat-completion';
    public const FEATURE_EXTRACTION = 'feature-extraction';
    public const FILL_MASK = 'fill-mask';
    public const IMAGE_CLASSIFICATION = 'image-classification';
    public const IMAGE_SEGMENTATION = 'image-segmentation';
    public const IMAGE_TO_TEXT = 'image-to-text';
    public const OBJECT_DETECTION = 'object-detection';
    public const QUESTION_ANSWERING = 'question-answering';
    public const SENTENCE_SIMILARITY = 'sentence-similarity';
    public const SUMMARIZATION = 'summarization';
    public const TABLE_QUESTION_ANSWERING = 'table-question-answering';
    public const TEXT_CLASSIFICATION = 'text-classification';
    public const TEXT_GENERATION = 'text-generation';
    public const TEXT_TO_IMAGE = 'text-to-image';
    public const TOKEN_CLASSIFICATION = 'token-classification';
    public const TRANSLATION = 'translation';
    public const ZERO_SHOT_CLASSIFICATION = 'zero-shot-classification';
}
