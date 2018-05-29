<?php

namespace SamTaylor\MasterMind\Commands;

use SamTaylor\MasterMind\Game;
use SamTaylor\MasterMind\Solver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WatchCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('watch');
        $this->addOption('set-answer', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $game = new Game();
        $solver = new Solver();

        if ($answer = $input->getOption('set-answer')) {
            $game->debug = true;
            $game->verbosity = 0;
            $game->setAnswer($answer);
        }

        $guess = $solver->initialGuess();

        while (!$game->correct || count($solver->pool) > 0) {
            $feedback = $game->guess($guess);

            $cells = [];
            foreach ($game->guesses as $previousGuess) {
                $cells[] = [
                    $previousGuess[1],
                    $previousGuess[0],
                    $previousGuess[2],
                ];
            }

            if (count($game->guesses) > 0) {
                $io->section('Guess #'.count($game->guesses));
                $io->table(['Correct', 'Guess', 'Close'], $cells);
            }

            if ($feedback === [-1, -1]) {
                break;
            } elseif ($feedback === [$game->spaces, 0]) {
                break;
            }

            $solver->setFeedback($feedback);
            $solver->newFilteredPool($guess);

            $io->text('Analyzing possible options...');

            $progress = new ProgressBar($output, count($solver->pool));
            $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %estimated:-6s%');

            $progress->start();

            $guess = $solver->makeGuess(function () use ($progress) {
                $progress->advance();
            });

            $progress->finish();
        }

        if ($game->correct) {
            $io->success([
                'SOLVED ON THE GUESS #'.count($game->guesses).'!',
                '['.implode(', ', $guess).']',
            ]);
        } else {
            $io->error([
                'COULD NOT SOLVE!',
                'Correct answer: ['.implode(', ', $game->getAnswer()).']',
            ]);
        }
    }
}
