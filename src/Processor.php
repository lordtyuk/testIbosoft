<?php

namespace TechBird\CsvTest;

include(dirname(__DIR__).'/src/Exception/InvalidFile.php');
include(dirname(__DIR__).'/src/HashInfo.php');

use TechBird\CsvTest\Exception\InvalidFile;

class Processor {

    /* @var HashInfo[] */
    private $hashList;

    /* @var string */
    private $inputFileName;

    /* @var string */
    private $outputFileName;

    public function __construct()
    {
        $this->hashList = new \ArrayObject();
        $this->inputFileName = '';
        $this->outputFileName = '';
    }

    public function setInputFileName($fileName)
    {
        $this->inputFileName = $fileName;
    }

    public function setOutputFileName($fileName)
    {
        $this->outputFileName = $fileName;
    }

    protected function readCSV()
    {
        if (($handle = @fopen($this->inputFileName, "r")) !== FALSE) {

            while (($data = @fgetcsv($handle, 0, ",")) !== FALSE) {

                $data = array_unique($data);
                sort($data);
                for($i = 0;$i<count($data);$i++) {
                    for($j=$i+1;$j<count($data);$j++) {

                        $key = sha1($data[$i].$data[$j]);
                        if(!$this->hashList->offsetExists($key)) {

                            $hashInfo = new HashInfo();
                            $hashInfo   ->addBand($data[$i])
                                        ->addBand($data[$j]);

                            $this->hashList->offsetSet($key, $hashInfo);
                        }

                        $this->hashList->offsetGet($key)->inc();

                    }
                }

            }

            fclose($handle);
        } else {
            throw new InvalidFile('Could not read input file');
        }
    }

    protected function writeCSV()
    {
        if (($handle = @fopen($this->outputFileName, "w")) !== FALSE) {

            foreach($this->hashList as $item) {
                @fwrite($handle, (string)$item);
            }

            fclose($handle);
        } else {
            throw new InvalidFile('Could not write output file');
        }

    }

    public function process()
    {
        $this->readCSV();
        $this->writeCSV();
    }
}