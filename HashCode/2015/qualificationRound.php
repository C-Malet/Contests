<?php

DEFINE('INIT', 'O');
DEFINE('INDISPO', 'X');
DEFINE('OCCUPIED', '$');

function initDrawable($maxline, $maxcol)
{
    $drawable = array();

    for ($i = 0; $i < $maxline; $i++) {
        for ($j = 0; $j < $maxcol; $j++) {
            $drawable[$i][$j] = INIT;
        }
    }

    return $drawable;
}

function parseInput()
{
    $filecontent = file('../input.txt');

    $firstline = array_shift($filecontent);
    list($nbrangee, $nbemplacement, $nbindispo, $nbgroup, $nbserver) = explode(' ', trim($firstline));


    $drawable = initDrawable($nbrangee, $nbemplacement);

    for ($i = 0; $i < $nbindispo; $i++) {
        $indispo = array_shift($filecontent);
        list($irangee, $icolumn) = explode(' ', trim($indispo));
        $drawable[$irangee][$icolumn] = INDISPO;
    }

    $servers = array();

    for ($k = 0; $k < $nbserver; ++$k) {
        $indispo = array_shift($filecontent);
        list($taille, $capacite) = explode(' ', trim($indispo));
        $servers[] = array(
            'taille'   => $taille,
            'capacite' => $capacite,
            'id' => $k
        );
    }

    return array(
        'nbgroup' => $nbgroup,
        'servers' => $servers,
        'dc'      => $drawable,
        'rang' => $nbrangee,
        'col' => $nbemplacement
    );
}

$globals = parseInput();

$rang = $globals['rang'];
$col = $globals['col'];

$nbg = $globals['nbgroup'];

$servers = $globals['servers'];
$baseServ = $servers;

$dc = $globals['dc'];
array_walk($dc, function (&$val) {
     $val = implode('', $val);
});

usort($servers, function ($a, $b) {
    return $a['capacite'] < $b['capacite'];
});

$groups = array();

$i = 0;
$sens = '+';
$nbServers = count($servers);
foreach ($servers as $server) {
    $groups[$i % $nbg][] = $server;
    if ($sens == '+')
        ++$i;
    else {
        --$i;
    }

    if ($i % $nbg < 0) {
        $sens = $sens == '+' ? '-' : '?';
    }
}

$addedServers = array();
$added = true;
$group = 44;

while ($added == true) {
    $added = false;
    for ($i = $rang - 1; $i >= 0; --$i) {

        if (!isset($groups[$group])) {
            $group = 44;
        }
        $server = array_shift($groups[$group]);
        $taille = $server['taille'];
        $dispo = strpos($dc[$i], INIT);

        if ($dispo === false) {
            array_unshift($groups[$group], $server);
            continue;
        }

        $next = substr($dc[$i], $dispo, $taille);

        if($dispo+$taille > strlen($dc[$i])) {
            array_unshift($groups[$group], $server);
                continue;
        }

        $found = false;
        while (!$found) {
            $posIndispo = strpos($next, INDISPO);
            $posOccupied = strpos($next, OCCUPIED);

            if ($posIndispo !== false || $posOccupied !== false) {
                if ($posIndispo === false) {
                    $min = $posOccupied;
                } else {
                    $min = $posIndispo;
                }

                if (!isset($min)) $min = min($posOccupied, $posIndispo);
                $dispo = strpos($dc[$i], INIT, $dispo + $min);
                //segment to allow
                unset($min);
                $next = substr($dc[$i], $dispo, $taille);

                //not dispo, next rang
                if ($dispo === false || $dispo+$taille > strlen($dc[$i])) {
                    $found= false;
                    break;
                }
            } else {
                $found = true;
            }
        }


        if ($found == false) {
            array_unshift($groups[$group], $server);
            continue;
        } else {
            $baseServ[$server['id']]['rang'] = $i;
            $baseServ[$server['id']]['col'] = $dispo;
            $baseServ[$server['id']]['group'] = $group;

            $dc[$i] = substr_replace($dc[$i], str_pad(OCCUPIED, $taille, OCCUPIED), $dispo, $taille);

            $group--;
            $added = true;
        }
    }

}

$output = '';

foreach ($baseServ as $serv) {
    if (!isset($serv['rang'])) {
        $output .= "x\n";
    } else {
        $output .= $serv['rang'] . ' ' . $serv['col'] . ' ' . $serv ['group'] . "\n";    }
}

file_put_contents('../output.txt', $output);