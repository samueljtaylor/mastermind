<?php

namespace SamTaylor\MasterMind;

use Tightenco\Collect\Support\Collection;

/**
 * MasterMind Game.
 */
class Game
{
    /**
     * Debug mode.
     *
     * @var bool
     */
    public $debug;

    /**
     * Valid Choices.
     *
     * @var Collection
     */
    public $choices;

    /**
     * Number of paces.
     *
     * @var int
     */
    public $spaces;

    /**
     * Every space should have a unique value.
     *
     * ie: [1,2,3,4] is unique | [1,1,2,3] is not
     *
     * @var bool
     */
    public $unique;

    /**
     * All the guesses in this game.
     *
     * @var Collection
     */
    public $guesses;

    /**
     * The answer.
     *
     * @var Collection
     */
    private $answer;

    /**
     * Game has been solved.
     *
     * @var bool
     */
    public $correct = false;

    /**
     * Game constructor.
     */
    public function __construct()
    {
        $config = new Config();
        $this->debug = $config->get('debug');
        $this->choices = collect($config->get('choices'));
        $this->spaces = $config->get('spaces');
        $this->unique = $config->get('generate_unique');

        $this->guesses = collect();

        $this->generate();
    }

    /**
     * Generate an answer.
     */
    public function generate()
    {
        $answer = collect();

        $choices = collect($this->choices->toArray());
        $choices->shuffle();

        if ($this->unique) {
            $answer = $choices->slice($this->choices->count() - $this->spaces);
        } else {
            for ($i = 0; $i < $this->spaces; $i++) {
                $answer->push($choices->get(rand(0, count($choices) - 1)));
            }
        }

        $this->answer = $answer;
    }

    /**
     * Validate a guess.
     *
     * @param array|string|null $guess
     *
     * @return array
     */
    public function guess($guess)
    {
        if ($guess === null) {
            return [-1, -1];
        }

        if (gettype($guess) === 'string') {
            $guess = collect(str_split(strtoupper($guess)));
        } elseif (gettype($guess) === 'array') {
            $guess = collect($guess);
        }

        if ($this->answer->all() === $guess->all()) {
            $this->guesses->push([
                implode(' | ', $guess->all()), $this->spaces, 0,
            ]);
            $this->correct = true;

            return [$this->spaces, 0];
        }

        $numCorrect = 0;
        $numClose = 0;

        $close = collect();
        $correct = collect();
        $incorrect = collect($guess->toArray());
        $found = collect($this->answer->toArray());

        foreach ($this->answer->all() as $index => $value) {
            if ($value === $guess->get($index)) {
                $correct->put($index, $value);
                $incorrect->put($index, null);
                $found->put($index, null);
                $numCorrect++;
            }
        }

        foreach ($incorrect->all() as $index => $value) {
            if ($value !== null) {
                $foundIndex = $found->search($value);
                if ($foundIndex !== false) {
                    $found->put($foundIndex, null);
                    $close->push($value);
                    $numClose++;
                }
            }
        }

        $this->guesses->push([
            implode(' | ', $guess->all()),
            $numCorrect,
            $numClose,
        ]);

        return [$numCorrect, $numClose];
    }

    /**
     * Get the answer.
     *
     * @return array
     */
    public function getAnswer()
    {
        if ($this->debug) {
            return $this->answer->all();
        }
    }

    /**
     * Set the answer.
     *
     * @param array|string $answer
     *
     * @return bool
     */
    public function setAnswer($answer)
    {
        if ($this->debug) {
            if (gettype($answer) === 'string') {
                $answer = str_split($answer);
            }
            $this->answer = collect($answer);

            return true;
        }

        return false;
    }

    /**
     * Debug Dumper.
     *
     * @param mixed ...$args
     */
    public function debug(...$args)
    {
        if ($this->debug) {
            dump(...$args);
        }
    }
}
