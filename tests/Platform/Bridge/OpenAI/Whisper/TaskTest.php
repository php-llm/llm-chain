<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Platform\Bridge\OpenAI\Whisper;

use PhpLlm\LlmChain\Platform\Bridge\OpenAI\Whisper\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Task::class)]
#[Small]
final class TaskTest extends TestCase
{
    #[Test]
    public function itDefinesTranscriptionTask(): void
    {
        self::assertSame('transcription', Task::TRANSCRIPTION);
    }

    #[Test]
    public function itDefinesTranslationTask(): void
    {
        self::assertSame('translation', Task::TRANSLATION);
    }
}