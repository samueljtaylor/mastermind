<?php

namespace SamTaylor\MasterMind\Commands;

use SamTaylor\MasterMind\Solver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SolverCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('play:computer');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $solver = new Solver();
        $guess = $solver->initialGuess();

        while (true) {
            $io->text('['.implode(', ', $guess).']');
            $correct = (int) $io->ask('How many correct?');
            $close = (int) $io->ask('How many close?');

            $solver->setFeedback($correct, $close);

            if ($solver->feedback['correct'] === $solver->spaces) {
                break;
            }

            $solver->newFilteredPool($guess);
            $io->text('Analyzing possible options...');
            $io->newLine();

            $progress = new ProgressBar($output, count($solver->pool));
            $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %estimated:-6s%');

            $progress->start();

            $guess = $solver->makeGuess(function () use ($progress) {
                $progress->advance();
            });

            $progress->finish();
            
            $io->text(PHP_EOL);
        }

        $io->text('Solved!');
    }
}
