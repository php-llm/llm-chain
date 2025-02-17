<?php

namespace PhpLlm\LlmChain\Platform;

interface RequestBodyProducer
{
    public function createBody(): array;
}
