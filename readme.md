# MasterMind

Based off the MasterMind board game, just for fun.

## Install

Clone the repository and run

```bash
$ composer install
```

## Solve the puzzle

From your terminal run

```bash
$ ./mastermind play:solve
```

Guess the code, the game will return if you have the correct number and spot (correct) or just the correct color but wrong spot (close)

```bash
# Example output

Choices
-------

 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8

 Guess:
 >
```

Enter the 4 (by default) numbers you want to guess as `1234`

```bash
# Example Output

Choices
-------

 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8

Guesses
-------

 --------- --------------- -------
  Correct   Guess           Close
 --------- --------------- -------
  0         1 | 2 | 3 | 4   1
 --------- --------------- -------

 Guess:
 >
 ```


## Have the computer solve your code

From the app directory run

```bash
$ ./mastermind play:computer
```

Think of (and write down) a 4 (by default) code using the valid characters from `config/config.php`

The computer will provide a guess and you type in how many are correct (correct color, correct spot) and how many are close (correct color, wrong spot)

## Watch

You can also watch the computer face itself by running

```bash
$ ./mastermind watch
```

Or to set your own answer

```bash
$ ./mastermind watch --set-answer=1234
```

## Config

In `config/config.php` you can set the valid characters, how long the codes should be (spaces) etc.

