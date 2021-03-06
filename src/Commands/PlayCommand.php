<?php

namespace SamTaylor\MasterMind\Commands;

use SamTaylor\MasterMind\Game;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PlayCommand extends Command
{
    /**
     * @var Game
     */
    protected $game;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('play:solve');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->game = new Game();

        $io = new SymfonyStyle($input, $output);

        while (!$this->game->correct) {
            $io->section('Choices');
            $io->text(implode(' | ', $this->game->choices->all()));

            $cells = [];
            foreach ($this->game->guesses->all() as $guess) {
                $cells[] = [
                    $guess[1],
                    $guess[0],
                    $guess[2],
                ];
            }

            if ($this->game->guesses->count()) {
                $io->section('Guesses');
                $io->table(['Correct', 'Guess', 'Close'], $cells);
            }

            $response = $io->ask('Guess');

            if ($response === 'exit') {
                $io->text('Exiting.');
                break;
            }

            $this->game->guess($response);
        }

        if ($this->game->correct) {
            $io->success(['Correct Guess!']);
        }
    }
}
