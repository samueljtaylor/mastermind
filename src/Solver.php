<?php


namespace SamTaylor\MasterMind;

class Solver
{
    /**
     * Debug mode
     *
     * @var bool
     */
    public $debug;

    /**
     * Valid choices
     *
     * @var array
     */
    public $choices;

    /**
     * Number of spaces
     *
     * @var int
     */
    public $spaces;

    /**
     * Guess pool (all possible answers)
     *
     * @var array
     */
    public $pool = [];

    /**
     * Current feedback from guess
     *
     * @var array
     */
    public $feedback = [
        'correct' => 0,
        'close' => 0,
    ];

    /**
     * Solver constructor.
     */
    public function __construct()
    {
        $config = new Config();
        $this->debug = $config->get('debug');
        $this->spaces = $config->get('spaces');
        $this->choices = $config->get('choices');


        $this->buildGuessPool();
    }

    /**
     * Set the feedback
     *
     * @param mixed $correct
     * @param int $close
     */
    public function setFeedback($correct, $close = null)
    {
        if(gettype($correct) === 'array') {
            $this->feedback['correct'] = $correct[0];
            $this->feedback['close'] = $correct[1];
        } else {
            $this->feedback['correct'] = $correct;
            $this->feedback['close'] = $close;
        }
    }

    /**
     * Build the possible guesses
     */
    public function buildGuessPool()
    {
        $cartesian = [];

        for($i = 0; $i < $this->spaces; $i++) {
            $cartesian[] = $this->choices;
        }

        $this->pool = $this->cartesian($cartesian);


    }

    /**
     * Cartesian product
     *
     * @param array $input
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

    /**
     * Generate an initial guess
     *
     * @return array
     */
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

    /**
     * Generate a guess
     *
     * @return array
     */
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

    /**
     * Filter pool for a guess
     *
     * @param array $guess
     */
    public function newFilteredPool($guess)
    {

        $this->pool = $this->filterGuessPool($this->pool, $guess);
    }

    /**
     * Filter the guess pool
     *
     * @param array $pool
     * @param array $guess
     * @return array
     */
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

    /**
     * Find the number of correct
     *
     * @param array $actual
     * @param array $guess
     * @return int
     */
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

    /**
     * Find the number of close
     *
     * @param array $actual
     * @param array $guess
     * @return int
     */
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

    /**
     * Remove the correct
     *
     * @param array $actual
     * @param array $guess
     * @return array
     */
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

    /**
     * Get feedback
     *
     * @param array $actual
     * @param array $guess
     * @return array
     */
    public function getFeedback($actual, $guess)
    {

        return [
            'correct' => $this->findCorrect($actual, $guess),
            'close' => $this->findClose($actual, $guess),
        ];
    }

    /**
     * Is a match?
     *
     * @param array $guess
     * @param array $item
     * @return bool
     */
    public function isMatch($guess, $item)
    {

        $feedback = $this->getFeedback($item, $guess);
        return ($feedback['correct'] === $this->feedback['correct']) && ($feedback['close'] === $this->feedback['close']);
    }

    /**
     * __get
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * Debug dumper
     *
     * @param mixed ...$args
     */
    public function debug(...$args)
    {
        if($this->debug) {
            dump(...$args);
        }
    }

}