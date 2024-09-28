<?php

declare(strict_types=1);

namespace PhpLlm\LlmChain\Voyage\Model;

use PhpLlm\LlmChain\Document\Vector;
use PhpLlm\LlmChain\EmbeddingsModel;
use PhpLlm\LlmChain\Voyage\Model\Voyage\Version;
use PhpLlm\LlmChain\Voyage\Platform;

final class Voyage implements EmbeddingsModel
{
    public function __construct(
        private readonly Platform $platform,
        private ?Version $version = null,
    ) {
        $this->version ??= Version::v3();
    }

    public function create(string $text, array $options = []): Vector
    {
        $vectors = $this->multiCreate([$text], $options);

        return $vectors[0];
    }

    public function multiCreate(array $texts, array $options = []): array
    {
        $response = $this->platform->request(array_merge($options, [
            'model' => $this->version->name,
            'input' => $texts,
        ]));

        return array_map(fn (array $data) => new Vector($data['embedding']), $response['data']);
    }
}
