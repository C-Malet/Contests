<?php
echo '<pre>';

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

include 'functions.php';
include 'DataCenter.php';
include 'Server.php';
include 'Group.php';

$file = file('../dc.in', FILE_IGNORE_NEW_LINES);
$tmpFile = $file;

$bestResult = null;
$bestScore = 0;

$count = 0;

    $file = $tmpFile;

    list (
        $nbLines,
        $nbCols,
        $nbIndispos,
        $nbGroups,
        $nbServers
    ) = explode (' ', array_shift($file));

    $indispos = initIndispos($file, $nbIndispos); // arr
    $servers = initServers($file, $nbServers); // arr

    $tmpServs = [];
    $cpt = 0;
    $serv = [];
    foreach ($servers as $k => $s) {
        if ($s->ratio() < 9) {
            ++$cpt;
            $tmpServs[] = $s;
            unset($servers[$k]);
        } else {
            $serv[] = $servers[$k];
        }
    }

    $servers = $serv;

    usort($servers, function ($a, $b) {
        return $a->ratio() < $b->ratio();
    });

    foreach ($tmpServs as $k => $s) {
        if ($s->length < 3) {
            $servers[] = $s;
            unset($tmpServs[$k]);
        }
    }

    $groups = initGroups($nbGroups); // arr

    $bestScore = 0;

while (true) {
    ++$count;


    $dc = new DataCenter($nbLines, $nbCols);
    $dc->addIndispos($indispos);

    sortServersByCapacity($servers);
    assignServersToGroups($servers, $groups);
    //maxCapacityByGroup($groups);

    $output = $dc->letsRock($groups, $tmpServs);
    //usedCapacityByGroup($groups);

    $values = $dc->score($groups);
    $score = $values['score'];

    /**
    for ($k = 0; $k < 10; $k++) {
        $dc->optimize($groups, $values['group'], $values['line']);
        $values = $dc->score($groups);
        echo $values['score'] . '-';
    }
    */


        $bestScore = $score;
        $bestOutput = $output;

    if ($count > 0) {
        break;
    }

    unset($groups);
    unset($dc);
}

file_put_contents('../output', $bestOutput);
var_dump($values);
echo PHP_EOL;
echo $dc;

?>