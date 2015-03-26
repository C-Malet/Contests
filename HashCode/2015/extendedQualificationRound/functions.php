<?php

    function initIndispos (&$file, $nbIndispos) {
        $indispos = [];
        for ($i = 0; $i < $nbIndispos; $i++) {
            $indispos[] = explode(' ', array_shift($file));
        }
        return $indispos;
    }

    function initServers (&$file, $nbServers) {
        $servers = [];
        for ($i = 0; $i < $nbServers; ++$i) {
            $server = explode(' ', array_shift($file));
            $servers[] = new Server($server[1], $server[0], $i);
        }
        return $servers;
    }

    function initGroups ($nbGroups) {
        $groups = [];
        for ($i = 0; $i < $nbGroups; ++$i) {
            $groups[$i] = null;
        }
        return $groups;
    }

    function sortServersByCapacity (&$servers) {
        //shuffle($servers);
        usort($servers, function ($sA, $sB) {
            return $sA->capacity < $sB->capacity;
            //return $sA->ratio() > $sB->ratio();
        });
    }

    function array_swap_assoc($key1, $key2, $array) {
        $newArray = array ();
        foreach ($array as $key => $value) {
            if ($key == $key1) {
                $newArray[$key2] = $array[$key2];
            } elseif ($key == $key2) {
                $newArray[$key1] = $array[$key1];
            } else {
                $newArray[$key] = $value;
            }
        }
        return $newArray;
    }

    function assignServersToGroups (array $servers, array &$groups) {
        /**$i = 0;
        $groupsCount = count($groups);
        $sens = 1;
        do {
            $groups[$i][] = array_shift($servers);

            $i += $sens;

            if ($i == 0 || $i == $groupsCount - 1) {
                $groups[$i][] = array_shift($servers);
                $sens = $sens == 1 ? -1 : 1;
            }
        } while (!empty($servers));
        */

        foreach ($groups as $k => &$g) {
            $i = 0;
            $capacity = 0;
            while ($capacity < 424 && $i < 35) {
                $s = array_shift($servers);
                if (!is_object($s)) break;
                $g[] = $s;
                $capacity += $s->capacity;
                $i++;
            }
        }

        $i = 0;

        while (!empty($servers)) {
            while (groupCapacity($groups[$i]) < 510 && !empty($servers)) {
                $groups[$i][] = array_shift($servers);
            }
            $i++;
            if ($i == count($groups)) break;
        }
    }

    function serverInLineForGroup($group, $line) {
        foreach ($group as $s) {
            if ($s->used && $s->line == $line) {
                return true;
            }
        }
        return false;
    }

    function bestLineForGroup($group, $linesExcluded, $cLine, $byPass, $server) {
        $capacities = array_fill(0, $cLine, 0);
        foreach ($group as $s) {
            if ($s->used) {
                if (!isset($capacities[$s->line])) {
                    $capacities[$s->line] = 0;
                }
                $capacities[$s->line] += $s->capacity;
            }
        }

        asort ($capacities);
        if (in_array(0, $capacities)) {
            $tab = [];
            foreach ($capacities as $k => $c) {
                if ($c == 0) {
                    $tab[] = $k;
                }
            }
            return $tab[rand(0, count($tab) - 1)];
        }
        $capacities = array_flip($capacities);

        foreach ($capacities as $l) {
            if (!in_array($l, $linesExcluded)) {
                return $l;
            }
        }
        if (!isset($capacities[0])) {
            return 0;
        }
        //return rand(0, $cLine - 1);
        return $capacities[0];
    }

    function groupCapacity($g) {
        $sum = 0;
        foreach ($g as $s) {
            $sum += $s->capacity;
        }
        return $sum;
    }

    function maxPotentialCapacityByGroup (array $groups) {
        foreach ($groups as $gKey => $g) {
            $sum = 0;
            $max = 0;
            foreach ($g as $s) {
                echo '-';
                if ($s->capacity > $max) $max = $s->capacity;
                $sum += $s->capacity;
            }
            echo $gKey . ' => ' . ($sum - $max) . '<br>';
        }
    }

    function maxCapacityByGroup (array $groups) {
        foreach ($groups as $gKey => $g) {
            $sum = 0;
            foreach ($g as $s) {
                echo '-';
                $sum += $s->capacity;
            }
            echo $gKey . ' => ' . $sum . '<br>';
        }
    }

    function usedCapacityForGroup ($group) {
            $sum = 0;
            foreach ($group as $s) {
                if ($s->used) {
                    $sum += $s->capacity;
                }
            }
            return $sum;
    }

    function usedCapacityByGroup (array $groups) {
        foreach ($groups as $gKey => $g) {
            $sum = 0;
            foreach ($g as $s) {
                if ($s->used) {
                    echo '-';
                    $sum += $s->capacity;
                }
            }
            echo $gKey . ' => ' . $sum . '<br>';
        }
    }

    function shuffle_assoc(&$array) {
        $keys = array_keys($array);

        shuffle($keys);

        foreach($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;

        return true;
    }

    function totalScore ($dc) {
        $score = 999999;

        foreach ($dc->groups as $group) {
            foreach ($dc->dc as $keyLine => $line) {
                $lineScore = $group->capacityWithoutLine($keyLine);
                if ($lineScore < $score) {
                    $score = $lineScore;
                    $lineId = $keyLine;
                    $groupId = $group->groupId;
                }
            }
        }

        return array(
            'score' => $score,
            'line' => $lineId,
            'group' => $groupId
        );
    }

    function worstGroup($groupz, $add) {
        $add = null;
        if ($add == null) {
            $cap = array_fill(0, count($groupz), 0);
            $gLines = [];
            foreach ($groupz as $iG => $g) {
                $lines = array_fill(0, 45, 0);
                $ca = 0;
                foreach ($g as $s) {
                    if ($s->used) {
                        $ca += $s->capacity;
                        $lines[$s->line] += $s->capacity;
                    }
                }
                $cap[$iG] = $ca - max($lines);
                $gLines[$iG] = array_keys($lines, max($lines))[0];
            }
            $gr = array_keys($cap, min($cap))[0];
            $li = $gLines[$gr];
            return array($gr, $li, min($cap));
        } else {
            $cap = array_fill(0, count($groupz), 0);
            $gLines = [];
            foreach ($groupz as $iG => $g) {
                if ($iG == $add) continue;
                $lines = array_fill(0, 45, 0);
                $ca = 0;
                foreach ($g as $s) {
                    if ($s->used) {
                        $ca += $s->capacity;
                        $lines[$s->line] += $s->capacity;
                    }
                }
                $cap[$iG] = $ca - max($lines);
                $gLines[$iG] = array_keys($lines, max($lines))[0];
            }
            sort ($cap);
            array_shift($cap);
            $gr = array_keys($cap, min($cap));
            $li = $gLines[$gr[0]];
            return array($gr, $li);
        }

    }

    function pickBestFromOtherGroups($groups, $group) {
        $server = null;
        $gr = null;
        foreach ($groups as $iG => $g) {
            if ($iG != $group) {
                foreach ($g as $s) {
                    if (!$s->used) {
                        if ($server === null) {
                            $server = $s;
                            $gr = $iG;
                        } else {
                            if ($s->ratio() > $server->ratio()) {
                                $server = $s;
                                $gr = $iG;
                                break 2;
                            } else if ($s->ratio() == $server->ratio()) {
                                if ($s->length < $s->length) {
                                    $gr = $iG;
                                    $server = $s;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
        }

        return array($server, $gr);
    }
?>