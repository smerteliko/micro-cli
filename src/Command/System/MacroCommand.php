<?php

namespace Smerteliko\MicroCli\Command\System;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Attributes\Argument;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
use Smerteliko\MicroCli\Style\Style;
use Smerteliko\MicroCli\Macro\MacroManager;

#[AsConsoleCommand(name: 'macro', description: 'Record and playback a sequence of console commands')]
class MacroCommand extends Command
{
    #[Argument(description: 'Action to perform (record, stop, run)', required: true)]
    public string $action;

    #[Argument(description: 'Name of the macro (required for record and run)', required: false, default: '')]
    public ?string $name;

    /**
     * @throws \JsonException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new Style($input, $output);

        $manager = new MacroManager();

        match (strtolower($this->action)) {
            'record' => $this->record($manager, $io),
            'stop'   => $this->stop($manager, $io),
            'run'    => $this->runMacro($manager, $io),
            default  => $io->error("Unknown action '{$this->action}'. Use record, stop, or run."),
        };

        return 0;
    }

    /**
     * @throws \JsonException
     */
    private function record(MacroManager $manager, Style $io): void
    {
        if (!$this->name) {
            $io->error("You must provide a macro name to record.");
            return;
        }
        $manager->startRecording($this->name);
        $io->success("Started recording macro: {$this->name}");
        $io->text("All subsequent commands will be saved. Run 'macro stop' to finish.");
    }

    /**
     * @throws \JsonException
     */
    private function stop(MacroManager $manager, Style $io): void
    {
        $name = $manager->stopRecording();
        if ($name) {
            $io->success("Macro '{$name}' saved successfully!");
        } else {
            $io->warning("No macro was currently being recorded.");
        }
    }

    private function runMacro(MacroManager $manager, Style $io): void
    {
        if (!$this->name) {
            $io->error("You must provide a macro name to run.");
            return;
        }

        $macro = $manager->getMacro($this->name);

        if ($macro === null) {
            $io->error("Macro '{$this->name}' not found.");
            return;
        }

        if (empty($macro)) {
            $io->warning("Macro '{$this->name}' is empty.");
            return;
        }

        $io->title("Running Macro: {$this->name}");
        $binPath = realpath($_SERVER['PHP_SELF']);

        foreach ($macro as $index => $cmdArgs) {
            $step = $index + 1;
            $io->text("<comment>Step {$step}:</comment> php bin/console {$cmdArgs}");

            $exitCode = $io->runProcess(sprintf('php %s %s', escapeshellarg($binPath), $cmdArgs));

            if ($exitCode !== 0) {
                $io->error("Macro aborted at step {$step} due to an error.");
                return;
            }
        }

        $io->success("Macro '{$this->name}' completed successfully!");
    }
}