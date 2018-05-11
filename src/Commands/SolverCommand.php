<?php


namespace SamTaylor\MasterMind\Commands;


use SamTaylor\MasterMind\Solver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SolverCommand extends Command
{
    protected function configure()
    {
        $this->setName('play:computer');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $solver = new Solver();
        $guess = $solver->initialGuess();

        while(true) {
            $io->text('['.implode(', ', $guess).']');
            $correct = (int) $io->ask('How many correct?');
            $close = (int) $io->ask('How many close?');

            $solver->setFeedback($correct, $close);

            if($solver->feedback['correct'] === $solver->spaces) {
                break;
            }

            $solver->setFilterGuessPool($solver->pool, $guess);
            $io->text(count($solver->pool) . ' possibilities. I\'m thinking...');
            $io->newLine();

            $guess = $solver->makeGuess();
        }

        $io->text('GOT IT');
    }
}