<?php

declare(strict_types=1);

use PhpLlm\LlmChain\Chain\Chain;
use PhpLlm\LlmChain\Chain\Toolbox\Attribute\AsTool;
use PhpLlm\LlmChain\Chain\Toolbox\ChainProcessor;
use PhpLlm\LlmChain\Chain\Toolbox\Toolbox;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\GPT;
use PhpLlm\LlmChain\Platform\Bridge\OpenAI\PlatformFactory;
use PhpLlm\LlmChain\Platform\Message\Message;
use PhpLlm\LlmChain\Platform\Message\MessageBag;

require_once dirname(__DIR__).'/../vendor/autoload.php';

// Albert API configuration
$albertApiKey = $_ENV['ALBERT_API_KEY'] ?? null;
$albertApiUrl = $_ENV['ALBERT_API_URL'] ?? null;

if (empty($albertApiKey)) {
    echo 'Please set the ALBERT_API_KEY environment variable.'.\PHP_EOL;
    exit(1);
}

if (empty($albertApiUrl)) {
    echo 'Please set the ALBERT_API_URL environment variable (e.g., https://your-albert-instance.com).'.\PHP_EOL;
    exit(1);
}

// Custom tool for French administrative information
#[AsTool('french_departments', 'Get information about French departments')]
final class FrenchDepartments
{
    private array $departments = [
        '75' => ['name' => 'Paris', 'region' => 'Île-de-France', 'prefecture' => 'Paris'],
        '13' => ['name' => 'Bouches-du-Rhône', 'region' => 'Provence-Alpes-Côte d\'Azur', 'prefecture' => 'Marseille'],
        '69' => ['name' => 'Rhône', 'region' => 'Auvergne-Rhône-Alpes', 'prefecture' => 'Lyon'],
        '31' => ['name' => 'Haute-Garonne', 'region' => 'Occitanie', 'prefecture' => 'Toulouse'],
        '44' => ['name' => 'Loire-Atlantique', 'region' => 'Pays de la Loire', 'prefecture' => 'Nantes'],
    ];

    /**
     * Get information about a French department by its number.
     *
     * @param string $departmentNumber The department number (e.g., "75" for Paris)
     */
    public function __invoke(string $departmentNumber): array
    {
        if (!isset($this->departments[$departmentNumber])) {
            return ['error' => "Department $departmentNumber not found in the database"];
        }

        return $this->departments[$departmentNumber];
    }
}

// Initialize Albert API
$platform = PlatformFactory::create(
    apiKey: $albertApiKey,
    baseUrl: rtrim((string) $albertApiUrl, '/').'/v1/',
);

$model = new GPT($_ENV['ALBERT_MODEL'] ?? 'albert-7b-v2');

// Set up toolbox with our custom tool
$tool = new FrenchDepartments();
$toolbox = Toolbox::create($tool);
$processor = new ChainProcessor($toolbox);

$chain = new Chain($platform, $model, [$processor], [$processor]);

$messages = new MessageBag(
    Message::forSystem('You are a helpful assistant for French administrative information.'),
    Message::ofUser('What is the prefecture of department 69?'),
);

$response = $chain->call($messages);

echo 'Albert API Tool Calling Response:'.\PHP_EOL;
echo '================================='.\PHP_EOL;
echo $response->getContent().\PHP_EOL;
