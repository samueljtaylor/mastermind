<?php


namespace SamTaylor\MasterMind;

class Solver
{
    public $debug;

    public $choices;

    public $spaces;

    public $pool = [];

    public $feedback = [
        'correct' => 0,
        'close' => 0,
    ];

    public function __construct()
    {
        $config = new Config();
        $this->debug = $config->get('debug');
        $this->spaces = $config->get('spaces');
        $this->choices = $config->get('choices');


        $this->buildGuessPool();
    }

    public function setFeedback($correct, $close)
    {

        $this->feedback['correct'] = $correct;
        $this->feedback['close'] = $close;
    }

    public function buildGuessPool()
    {
        $cartesian = [];

        for($i = 0; $i < $this->spaces; $i++) {
            $cartesian[] = $this->choices;
        }

        $this->pool = $this->cartesian($cartesian);


    }

    /**
     * @param $input
     * @return array
     * @see https://stackoverflow.com/a/15973172
     */
    public function cartesian($input) {
        // filter out empty values
        $input = array_filter($input);

        $result = array(array());

        foreach ($input as $key => $values) {
            $append = array();

            foreach($result as $product) {
                foreach($values as $item) {
                    $product[$key] = $item;
                    $append[] = $product;
                }
            }

            $result = $append;
        }

        return $result;
    }

    public function initialGuess()
    {
        $guess = [];
        $mean = $this->spaces / 2;

        for ($i = 0; $i < $this->spaces; $i++) {
            if ($i < $mean) {
                $guess[] = $this->choices[0];
            } else {
                $guess[] = $this->choices[1];
            }
        }

        return $guess;
    }

    public function makeGuess()
    {

        $minLength = INF;
        $choice = null;

        foreach($this->pool as $index => $item) {
            $length = count($this->filterGuessPool($this->pool, $item));
            if($minLength > $length) {
                $minLength = $length;
                $choice = $item;
            }
        }

        return $choice;
    }

    public function setFilterGuessPool($pool, $guess)
    {

        $this->pool = $this->filterGuessPool($pool, $guess);
    }

    public function filterGuessPool($pool, $guess)
    {

        $output = [];
        foreach($pool as $index => $item) {
            if($this->isMatch($guess, $item) && $item !== $guess) {
                $output[] = $item;
            }
        }

        return $output;
    }

    public function findCorrect($actual, $guess)
    {

        $correct = 0;
        foreach($actual as $index => $item) {
            if($item === $guess[$index]) {
                $correct++;
            }
        }

        return $correct;
    }

    public function findClose($actual, $guess)
    {

        $removed = $this->removeCorrect($actual, $guess);
        $actual = $removed[0];
        $guess = $removed[1];

        $close = 0;

        foreach($guess as $index => $item) {
            if(in_array($item, $actual)) {
                unset($actual[array_search($item, $actual)]);
                $close++;
            }
        }

        return $close;
    }

    public function removeCorrect($actual, $guess)
    {

        $newActual = [];
        $newGuess = [];

        foreach($actual as $index => $item) {
            if($item !== $guess[$index]) {
                $newActual[] = $item;
                $newGuess[] = $guess[$index];
            }
        }


        return [ $newActual, $newGuess ];
    }

    public function getFeedback($actual, $guess)
    {

        return [
            'correct' => $this->findCorrect($actual, $guess),
            'close' => $this->findClose($actual, $guess),
        ];
    }

    public function isMatch($guess, $item)
    {

        $feedback = $this->getFeedback($item, $guess);
        return ($feedback['correct'] === $this->feedback['correct']) && ($feedback['close'] === $this->feedback['close']);
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function debug(...$args)
    {
        if($this->debug) {
            dump(...$args);
        }
    }

}