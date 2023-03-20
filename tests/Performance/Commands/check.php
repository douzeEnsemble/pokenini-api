<?php

declare(strict_types=1);

if (!isset($argv[1])) {
    throw new \InvalidArgumentException('You must set the number of turn for the avegare');
}

$nbExecution = (int) $argv[1];

$resultFileDir = 'tests/Performance/Commands/Results';

$files = [
    'update_labels' => 1.9,
    'update_games_and_dex' => 2,
    'update_pokemons' => 9.5,
    'update_regional_dex_numbers' => 2.5,
    'update_games_availabilities' => 12,
    'calculate_game_bundles_availabilities' => 3.5,
    'calculate_dex_availabilities' => 20,
];

$score = 0;

foreach ($files as $file => $maxExecutionTime) {
    $executionTimes = [];
    for ($i = 1; $i <= $nbExecution; $i++) {
        $executionTime = file_get_contents("$resultFileDir/$file-$i.txt");

        if (false === $executionTime) {
            throw new \RuntimeException("Can't read $file");
        }

        $executionTimes[] = (float) $executionTime;
    }

    $averageExecutionTime = array_sum($executionTimes) / $nbExecution;

    printf(
        "%s (avg %f)\n",
        $file,
        $averageExecutionTime,
    );

    if ((float) $averageExecutionTime >= $maxExecutionTime) {
        printf(
            "%s (avg %f) is greater than %f\n",
            $file,
            $averageExecutionTime,
            $maxExecutionTime,
        );
        $score++;
    }
}

echo $score;
