<?php

namespace App\Commands;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
use Smerteliko\MicroCli\Style\Style;

#[AsConsoleCommand( name: 'setup', description: 'Interactive project setup wizard', hidden: TRUE )]
class SetupCommand extends Command {
    public function execute(InputInterface $input,
                            OutputInterface $output): int {
        $io = new Style($input, $output);

        $io->title('Project Setup Wizard');

        $environment = $io->choice('Select the environment for this project:',
                                   [ 'local', 'staging', 'production' ],
                                   'local' // default
        );

        $io->text("Setting up <info>{$environment}</info> environment...");

        $modules = $io->choice('Which modules do you want to install?',
                               [ 'Redis Cache',
                                 'MySQL Database',
                                 'ElasticSearch',
                                 'RabbitMQ' ],
                               '1,2',
                               TRUE);

        $io->text("Installing modules: <info>" . implode(', ',
                                                         $modules) . "</info>");

        $password = $io->askHidden('Please enter the database password');

        if ($io->confirm("Initialize database with this password?", TRUE)) {
            $io->success("Database initialized for {$environment}!");
        } else {
            $io->warning("Database initialization skipped.");
        }

        return 0;
    }
}