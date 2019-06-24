<?php

namespace TechBird\CsvTest;


class HashInfo {

    private $bands;
    private $count;

    public function __construct()
    {
        $this->bands = new \ArrayObject();
        $this->count = 0;
    }

    public function addBand($bandName)
    {
        $this->bands->append($bandName);

        return $this;
    }

    public function inc()
    {
        $this->count++;
    }

    public function __toString()
    {
        if($this->count >= 50) {
            return implode(',',$this->bands->getArrayCopy())."\n";
        }

        return '';
    }
}