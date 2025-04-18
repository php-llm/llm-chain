<?php

use PhpLlm\LlmChain\Bridge\HuggingFace\ApiClient;
use PhpLlm\LlmChain\Bridge\HuggingFace\Model;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once dirname(__DIR__, 2).'/vendor/autoload.php';

$app = (new SingleCommandApplication('HuggingFace Model Listing'))
    ->setDescription('Lists all available models on HuggingFace')
    ->addOption('provider', 'p', InputOption::VALUE_REQUIRED, 'Name of the inference provider to filter models by')
    ->addOption('task', 't', InputOption::VALUE_REQUIRED, 'Name of the task to filter models by')
    ->setCode(function (InputInterface $input, ConsoleOutput $output) {
        $io = new SymfonyStyle($input, $output);
        $io->title('HuggingFace Model Listing');

        $provider = $input->getOption('provider');
        $task = $input->getOption('task');

        $models = (new ApiClient())->models($provider, $task);

        if (0 === count($models)) {
            $io->error('No models found for the given provider and task.');

            return Command::FAILURE;
        }

        $io->listing(
            array_map(fn (Model $model) => $model->getName(), $models)
        );

        return Command::SUCCESS;
    })
    ->run();
