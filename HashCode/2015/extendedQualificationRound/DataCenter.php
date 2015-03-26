<?php

class DataCenter {

    public $INIT = '_';
    public $OCC = 'O';
    public $INDISPO = 'X';

    private $nbSpaces = 0;
    private $nbSpacesUsed = 0;

    private $line = 0;

    public $dc = [];

    public function __construct($nbLines, $nbCols) {
        for ($i = 0; $i < $nbLines; ++$i) {
            for ($j = 0; $j < $nbCols; ++$j) {
                $this->dc[$i][$j] = $this->INIT;
                ++$this->nbSpaces;
            }
        }
    }

    public function __toString() {
        $str = '';
        foreach ($this->dc as $line) {
            foreach ($line as $col) {
                $str .= $col;
            }
            $str .= '<br>';
        }
        return $str;
    }

    public function addIndispos($indispos) {
        foreach ($indispos as $indispo) {
            $line = $indispo[0];
            $col = $indispo[1];
            $this->dc[$line][$col] = $this->INDISPO;
            ++$this->nbSpacesUsed;
        }
    }

    public function letsRock(array $groups, $tmpServs) {
/**
        $iG = 0;
        $countG = count($groups);
        while ($this->nbSpacesUsed < $this->nbSpaces) {
            if ($iG == $countG) {
                $iG = 0;
            }

            if (($server = $this->pickServerFromGroup($groups[$iG])) !== false) {

                $range = range($this->line, count($this->dc) - 1);
                foreach (range(0, count($this->dc) - 1) as $v) {
                    if (!in_array($v, $range)) {
                        $range[] = $v;
                    }
                }

                uksort($this->dc, function ($a, $b) use ($range) {
                    return array_keys($range, $a) > array_keys($range, $b);
                });

                foreach ($this->dc as $lKey => &$l) {
                    if (($pos = $this->findFreePositionInLine($l, $server)) === false) {
                        continue;
                    }

                    $server->used($lKey, $pos, $iG);
                    for ($i = $pos; $i < $pos + $server->length; ++$i) {
                        $l[$i] = $this->OCC;
                    }
                    $this->nbSpacesUsed += $server->length;

                    break;
                }
                ++$this->line;
                if ($this->line == count($this->dc)) {
                    $this->line = 0;
                }
                $server->treated = true;
            }

            ++$iG;
            if ($this->emptyGroups($groups) === true) {
                break;
            }
        }
*/


            usort ($groups, function ($gA, $gB) {
                return count($gA) > count($gB);
            });

            $startFrom = 0;
            foreach ($groups as $iG => $g) {
                if (count($g) < 8) {
                    $line = $startFrom;
                    while (($server = $this->pickServerFromGroup($g)) !== false && $server->length > 3) {
                        if (($pos = $this->findFreePositionInLine($this->dc[$line], $server)) !== false) {
                            $server->used($line, $pos, $iG);
                            for ($i = $pos; $i < $pos + $server->length; ++$i) {
                                $this->dc[$line][$i] = $this->OCC;
                            }
                            $this->nbSpacesUsed += $server->length;
                        }
                        $line += 2;

                        $line = $line >= count($this->dc) ? $startFrom + 1 : $line;
                    }
                    $startFrom = $startFrom == 0 ? 1 : 0;
                    //$this->dc = array_reverse($this->dc);
                }
            }

            echo $this;

            $line = 0;
            //$groups = array_reverse($groups);
            foreach ($groups as $iG => $g) {
                    $to = 0;
                    $byPass = false;
                    $excludedLines = [];
                    $tmpLine = 0;
                    while (usedCapacityForGroup($g) < 438 && ($server = $this->pickServerFromGroup($g)) !== false) {
                        $to++;

                        if (!$byPass) {
                            $line = bestLineForGroup($g, $excludedLines, count($this->dc), $byPass, $server);
                        }
                        $excludedLines[] = $line;
                        if (count($excludedLines) == count($this->dc)) {
                            $excludedLines = [];
                            $byPass = true;
                            $line = 0;
                        }

                        if (($pos = $this->findFreePositionInLine($this->dc[$line], $server)) !== false) {
                            $server->used($line, $pos, $iG);
                            for ($i = $pos; $i < $pos + $server->length; ++$i) {
                                $this->dc[$line][$i] = $this->OCC;
                            }
                            $this->nbSpacesUsed += $server->length;
                        }
                        $line += 1;
                        $line = $line >= count($this->dc) ? 0 : $line;
                        if ($to == 100) {
                            break;
                        }
                    }

                    $to = 0;
                    //$this->dc = array_reverse($this->dc);
            }
            echo $this;
            //usedCapacityByGroup($groups);


            $to = 100;

            $this->groups = $groups;
            print_r($this->score($groups));
            $tmpGroups = $groups;

            $addTo = [];
            $lastAdded = null;
            $grTmp = null;
            $i = 0;
            foreach ($groups as $tmp) {
                foreach ($tmp as $tmpB) {
                    $i++;
                }
            }
            foreach ($tmpServs as $s) {
                $i++;
            }
            echo $i;
            do {
                $groupe = worstGroup($groups, $lastAdded);
                $added = false;
                $tmp = $groupe;
                $serverez = pickBestFromOtherGroups($groups, $groupe[0], $groupe[1]);
                $servert = $serverez[0];
                $grTmp = $serverez[1];
                foreach ($this->dc as $kL => $l) {
                    if ($kL == $groupe[1]) {
                        continue;
                    }
                    if (($pos = $this->findFreePositionInLine($this->dc[$kL], $servert, true)) !== false) {
                        $servert->used($kL, $pos, $groupe[0]);
                        for ($i = $pos; $i < $pos + $servert->length; ++$i) {
                            $this->dc[$kL][$i] = $this->OCC;
                        }
                        $this->nbSpacesUsed += $servert->length;
                        $groups[$groupe[0]][] = $servert;
                        foreach($groups as $kGp => &$gps) {
                            if ($kGp == $grTmp) {
                                foreach ($gps as $o => $c) {
                                    if ($c->serverId == $servert->serverId) {

                                        unset($gps[$o]);
                                        break 2;
                                    }
                                }
                            }
                        }

                        $added = true;
                        break;
                    }
                }
            } while ($added == true && ++$to != 1000);

            $i = 0;
            foreach ($groups as $tmp) {
                foreach ($tmp as $tmpB) {
                    $i++;
                }
            }
            foreach ($tmpServs as $s) {
                $i++;
            }
            echo $i;

        var_dump($groups === $tmpGroups);
        $this->groups = $groups;

        var_dump($this->score($this->groups));

        return $this->output($groups, $tmpServs);
    }

    public function output($groups, $tmpServs) {
        $servers = [];
        foreach ($this->groups as $g) {
            foreach ($g as $s) {
                $servers[] = $s;
            }
        }

        $servers = array_merge($servers, $tmpServs);

        usort($servers, function ($a, $b) {
            return $a->serverId > $b->serverId;
        });

        $output = '';
        foreach ($servers as $s) {
            if ($s->used) {
                $output .= implode(' ', array(
                        $s->line,
                        $s->col,
                        $s->groupId
                    )) . "\n";
            } else {
                $output .= "x\n";
            }
        }

        return $output;
    }

    public function emptyGroups ($groups) {
        foreach ($groups as $g) {
            foreach ($g as $k => $s) {
                if (!$s->used && !$s->treated) {
                    return $s->serverId;
                }
            }
        }
        return true;
    }

    public function optimize(&$groups, $group, $line) {
        $candidates = [];
        $groupToModify = null;
        foreach ($groups as $kG => $g) {
            if ($kG != $group) {
                foreach ($g as &$s) {
                    if ($s->used === true) {
                        $candidates[] = $s;
                    }
                }
            } else {
                $groupToModify = $g;
            }
        }

        $groupedCandidates = [];
        foreach ($candidates as $c) {
            $groupedCandidates[$c->length][] = $c;
        }

        foreach ($groupedCandidates as &$o) {
            usort($o, function ($oA, $oB) {
                return $oA->capacity > $oB->capacity;
            });
        }

        foreach ($groupToModify as $s) {
            if ($s->used && $s->line == $line) {
                foreach ($groupedCandidates as $length => &$c) {
                    if ($length == $s->length && $c[0]->capacity < $s->capacity) {
                        $tmpLine = $c[0]->line;
                        $tmpCol = $c[0]->col;
                        $tmpGroupId = $c[0]->groupId;
                        $c[0]->line = $s->line;
                        $c[0]->col = $s->col;
                        $c[0]->groupId = $s->groupId;
                        $s->line = $tmpLine;
                        $s->col = $tmpCol;
                        $s->groupId = $tmpGroupId;
                        //echo 'replaced ' . $c[0]->serverId . ' of ' . $s->serverId . '<br>';
                        break 2;
                    }
                }
            }
        }
    }

    public function score($groups) {
        $score = 9999;
        $line = null;
        $group = null;
        foreach ($groups as $kG => $g) {
            $capacitiesPerLine = [];
            foreach ($this->dc as $lKey => $l) {
                $capacitiesPerLine[$lKey] = 0;
                foreach ($g as $s) {
                    if ($s->used && $s->line == $lKey) {
                        $capacitiesPerLine[$lKey] += $s->capacity;
                    }
                }
            }
            if ($score > array_sum($capacitiesPerLine) - max($capacitiesPerLine)) {
                $line = array_keys($capacitiesPerLine, max($capacitiesPerLine))[0];
                $group = $kG;
                $score = array_sum($capacitiesPerLine) - max($capacitiesPerLine);
            }
        }

        return array (
            'score' => $score,
            'group' => $group,
            'line'  => $line
        );

    }

    public function findFreePositionInLine(array $line, Server $server, $b = false) {
        $i = 0;
        $wantedI = $server->length;
        foreach ($line as $key => $c) {
            if ($c == $this->INIT) {
                ++$i;
                if ($i == $wantedI) {
                    return $key - ($wantedI - 1);
                }
            } else {
                $i = 0;
            }
        }
        return false;
    }

    public function pickServerFromGroup ($servers) {
        $candidates = [];
        foreach ($servers as $s) {
            if (!$s->used && !$s->treated) {
                $candidates[$s->ratio()][] = $s;
            }
        }

        if (empty($candidates)) {
            return false;
        }

        uksort($candidates, function ($kA, $kB) {
            return $kA > $kB;
        });
        $max = max(array_keys($candidates));

        usort ($candidates[$max], function ($sA, $sB) {
            return $sA->length < $sB->length;
        });

        return $candidates[$max][0];
    }

    private function toFill() {
        foreach ($this->dc as $line) {
            foreach ($line as $col) {
                if ($col == $this->INIT) {
                    return true;
                }
            }
        }
        return false;
    }

}

?>