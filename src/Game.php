<?php

namespace SamTaylor\MasterMind;

use Tightenco\Collect\Support\Collection;

class Game
{
    public $debug;

    /**
     * @var Collection
     */
    public $choices;

    public $spaces;

    public $unique;

    /**
     * @var Collection
     */
    public $guesses;

    /**
     * @var Collection
     */
    private $answer;

    public $correct = false;

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

    public function generate()
    {
        $answer = collect();

        $choices = collect($this->choices->toArray());
        $choices->shuffle();

        if($this->unique) {
            $answer = $choices->slice($this->choices->count() - $this->spaces);
        } else {
            for($i = 0; $i < $this->spaces; $i++) {
                $answer->push($choices->get(rand(0, count($choices)  - 1)));
            }
        }

        $this->answer = $answer;
    }

    public function guess($guess)
    {
        if($guess === null) {
            return [-1, -1];
        }

        if(gettype($guess) === 'string') {
            $guess = collect(str_split(strtoupper($guess)));
        } elseif(gettype($guess) === 'array') {
            $guess = collect($guess);
        }

        if($this->answer->all() === $guess->all()) {
            $this->guesses->push([
                implode(' | ', $guess->all()), $this->spaces, 0
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

        foreach($this->answer->all() as $index => $value) {
            if($value === $guess->get($index)) {
                $correct->put($index, $value);
                $incorrect->put($index, null);
                $found->put($index, null);
                $numCorrect++;
            }
        }


        foreach($incorrect->all() as $index => $value) {
            if($value !== null) {
                $foundIndex = $found->search($value);
                if($foundIndex !== false) {
                    $found->put($foundIndex, null);
                    $close->push($value);
                    $numClose++;
                }
            }
        }

        $response = [
            implode(' | ', $guess->all()),
            $numCorrect,
            $numClose,
        ];

        $this->guesses->push($response);

        if($numCorrect === 4) {
            $this->correct = true;
        }

        return [ $numCorrect, $numClose ];
    }

    public function answerHasDuplicates()
    {
        return $this->answer->count() > $this->answer->unique()->count();
    }

    public function getAnswer()
    {
        if($this->debug) {
            return $this->answer->all();
        }
        return null;
    }

    public function setAnswer($answer)
    {
        if($this->debug) {
            if(gettype($answer) === 'string') {
                $answer = str_split($answer);
            }
            $this->answer = collect($answer);
            return true;
        }
        return false;
    }

    public function debug(...$args)
    {
        if($this->debug) {
            dump(...$args);
        }
    }
}