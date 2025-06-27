<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Platform\Bridge\Google;

use PhpLlm\LlmChain\Platform\Bridge\Google\Embeddings\TaskType;
use PhpLlm\LlmChain\Platform\Capability;
use PhpLlm\LlmChain\Platform\Model;

/**
 * @author Valtteri R <valtzu@gmail.com>
 */
class Embeddings extends Model
{
    /** Supported dimensions: 3072, 1536, or 768 */
    public const GEMINI_EMBEDDING_EXP_03_07 = 'gemini-embedding-exp-03-07';
    /** Fixed 768 dimensions */
    public const TEXT_EMBEDDING_004 = 'text-embedding-004';
    /** Fixed 768 dimensions */
    public const EMBEDDING_001 = 'embedding-001';

    /**
     * @param array{dimensions?: int, task_type?: TaskType|string} $options
     */
    public function __construct(string $name = self::GEMINI_EMBEDDING_EXP_03_07, array $options = [])
    {
        parent::__construct($name, [Capability::INPUT_MULTIPLE], $options);
    }
}
