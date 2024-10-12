<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Tests\Fixture;

final class FaqItem
{
    public string $question;
    public string $answer;
    public ?string $url = null;
}
