<?php

$file = file('A-small-practice.in', FILE_IGNORE_NEW_LINES);

$nbTestCases = array_shift($file);

$cases = [];

for ($i = 0; $i < $nbTestCases; ++$i) {
    $cases[$i][0]['row'] = array_shift($file);
    for ($j = 0; $j < 4; ++$j) {
        $cases[$i][0][$j] = array_shift($file);
    }
    $cases[$i][1]['row'] = array_shift($file);
    for ($j = 0; $j < 4; ++$j) {
        $cases[$i][1][$j] = array_shift($file);
    }
}

$finalOutput = '';
foreach ($cases as $key => $case) {
    $output = 'Case #' . ($key + 1) . ': ';

    $firstCards = explode(' ', $case[0][$case[0]['row'] - 1]);
    $secondCards = explode(' ', $case[1][$case[1]['row'] - 1]);

    $intersect = array_intersect($firstCards, $secondCards);

    // Cheater
    if (empty($intersect)) {
        $output .= 'Volunteer cheated!';
        $finalOutput .= $output . PHP_EOL;
        continue;
    }

    // Bad magician
    if (count($intersect) !== 1) {
        $output .= 'Bad magician!';
        $finalOutput .= $output . PHP_EOL;
        continue;
    }

    $output .= array_shift($intersect);
    $finalOutput .= $output . PHP_EOL;
    continue;
}

file_put_contents('result.out', $finalOutput);

?>