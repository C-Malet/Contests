<?php

class Group {

    public $servers = [];
    public $usedServers = [];
    public $assignedLines = [];

    public function __construct ($groupId) {
        $this->groupId = $groupId;
    }

    public function currentCapacity() {
        return array_reduce($this->servers, function ($capacity, $server) {
            return $capacity + $server->capacity;
        }, 0);
    }

    public function currentUsedCapacity() {
    return array_reduce($this->usedServers, function ($capacity, $server) {
            return $capacity + $server->capacity;
        }, 0);
    }

    public function capacityWithoutLine($line) {
        return array_reduce($this->usedServers, function ($capacity, $server) use ($line) {
           if ($server->used == false || $server->line == $line) {
               return $capacity;
           }
           return $capacity + $server->capacity;
        }, 0);
    }

    public function addServer ($server) {
        $this->servers[] = $server;
    }

    public function findBestLines($countLines) {
        if (count($this->assignedLines) == $countLines) {
            $this->assignedLines = [];
        }

        do {
            $line = rand(0, $countLines - 1);
        } while (in_array($line, $this->assignedLines));
        $this->assignedLines[] = $line;

        return array($line);
    }

    public function bestRemainingServer() {
        // RANDOM BEST, bad as hell
        /*$tmpServers = $this->servers;
        shuffle($tmpServers);
        $serv = array_shift($tmpServers);
        $this->usedServers[] = $serv;
        foreach ($this->servers as $key => $server) {
            if ($server === $serv) {
                unset($this->servers[$key]);
                return $serv;
            }
        }
        return null;*/

        if (empty($this->servers)) {
            return null;
        }

        foreach ($this->servers as $server) {
            if (!isset($bestRatio)) {
                $bestRatio = $server->capacity / $server->length;
                continue;
            }

            $bestRatio = $server->capacity / $server->length > $bestRatio ?
                            $server->capacity / $server->length :
                            $bestRatio;
        }

        foreach ($this->servers as $key => $server) {
            if ($server->capacity / $server->length != $bestRatio) {
                continue;
            }

            if (!isset($best)) {
                $best = $server;
                $keyBest = $key;
                continue;
            }

            if ($server->length < $best->length) {
                $best = $server;
                $keyBest = $key;
            }
        }

        $this->usedServers[] = $best;
        unset($this->servers[$keyBest]);

        return $best;
    }

}

?>