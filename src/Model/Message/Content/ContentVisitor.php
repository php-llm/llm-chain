<?php

namespace PhpLlm\LlmChain\Model\Message\Content;

interface ContentVisitor
{
    public function visitAudio(Audio $content): array;

    public function visitImage(Image $content): array;

    public function visitText(Text $content): array;
}
