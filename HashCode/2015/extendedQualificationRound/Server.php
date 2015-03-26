<?php

class Server {

    public $used = false;
    public $treated = false;
    public $groupId = null;

    public function __construct($capacity, $length, $serverId) {
        $this->capacity = $capacity;
        $this->length = $length;
        $this->serverId = $serverId;
    }

    public function ratio() {
        return $this->capacity / $this->length;
    }

    public function used($line, $col, $groupId) {
        $this->used = true;
        $this->line = $line;
        $this->col = $col;
        $this->groupId = $groupId;
    }

    public function unuse() {
        $this->used = false;
    }

}

?>