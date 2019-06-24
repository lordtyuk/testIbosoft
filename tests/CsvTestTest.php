<?php

include(dirname(__DIR__).'/src/Processor.php');

class CsvTestTest extends \PHPUnit\Framework\TestCase {

    public function testReadCsvError()
    {
        $processor = new \TechBird\CsvTest\Processor();
        $processor->setInputFileName('nonExistingFile.csv');

        $this->expectException(\TechBird\CsvTest\Exception\InvalidFile::class);
        $processor->process();
    }

    public function testEmptyCsv()
    {
        $processor = new \TechBird\CsvTest\Processor();
        $processor->setInputFileName(dirname(__DIR__).'/tests/data/empty.csv');
        $processor->setOutputFileName(dirname(__DIR__).'/tests/output.csv');

        $processor->process();

        $this->assertTrue(file_exists(dirname(__DIR__).'/tests/output.csv'));
        $this->assertEmpty(file_get_contents(dirname(__DIR__).'/tests/output.csv'));

        unlink(dirname(__DIR__).'/tests/output.csv');
    }

    public function testRandomCsv()
    {

        $faker = Faker\Factory::create();

        $_bandPairs = $this->generateRandomFile($faker);

        $processor = new \TechBird\CsvTest\Processor();
        $processor->setInputFileName(dirname(__DIR__).'/tests/generated.csv');
        $processor->setOutputFileName(dirname(__DIR__).'/tests/output.csv');

        $processor->process();

        $this->assertTrue(file_exists(dirname(__DIR__).'/tests/output.csv'));

        if (($handle = @fopen(dirname(__DIR__).'/tests/output.csv', "r")) !== FALSE) {

            $foundPairs = 0;
            while (($data = @fgetcsv($handle, 0, ",")) !== FALSE) {

                $this->assertEquals(2, count($data));
                $this->assertNotFalse(array_search($data[0].','.$data[1], array_column($_bandPairs, 'name')) !== FALSE || array_search($data[1].','.$data[0], array_column($_bandPairs, 'name')) !== FALSE);
                $foundPairs++;
            }

            $this->assertEquals($foundPairs, count($_bandPairs));

            fclose($handle);
        }

        unlink(dirname(__DIR__).'/tests/output.csv');
        unlink(dirname(__DIR__).'/tests/generated.csv');
    }

    public function testRandomCsvSpecialChars()
    {

        $faker = Faker\Factory::create();
        $faker->addProvider(new Faker\Provider\zh_CN\Person($faker));

        $_bandPairs = $this->generateRandomFile($faker);


        $processor = new \TechBird\CsvTest\Processor();
        $processor->setInputFileName(dirname(__DIR__).'/tests/generated.csv');
        $processor->setOutputFileName(dirname(__DIR__).'/tests/output.csv');

        $processor->process();

        $this->assertTrue(file_exists(dirname(__DIR__).'/tests/output.csv'));

        if (($handle = @fopen(dirname(__DIR__).'/tests/output.csv', "r")) !== FALSE) {

            $foundPairs = 0;
            while (($data = @fgetcsv($handle, 0, ",")) !== FALSE) {

                $this->assertEquals(2, count($data));
                $this->assertNotFalse(array_search($data[0].','.$data[1], array_column($_bandPairs, 'name')) !== FALSE || array_search($data[1].','.$data[0], array_column($_bandPairs, 'name')) !== FALSE);
                $foundPairs++;
            }

            $this->assertEquals($foundPairs, count($_bandPairs));

            fclose($handle);
        }


        unlink(dirname(__DIR__).'/tests/output.csv');
        unlink(dirname(__DIR__).'/tests/generated.csv');
    }


    private function generateRandomFile(Faker\Generator $faker)
    {
        $bands = [];
        $singleBands = [];
        for($i=0;$i<90;$i++) {

            $bands[] = $faker->unique()->name;
            $singleBands[] = ['name' => $faker->unique()->name, 'count' => rand(20, 40)];
        }

        $this->assertEquals(180, count(array_unique(array_merge($bands, array_column($singleBands, 'name')))));

        $bandPairs = [];
        $requiredPairs = rand(30,50);


        while(count($bandPairs) < $requiredPairs) {
            $bandA = rand(0, 59);
            $bandB = rand(0, 59);

            if($bandA != $bandB && (array_search($bands[$bandA].','.$bands[$bandB], array_column($bandPairs, 'name')) === FALSE && array_search($bands[$bandB].','.$bands[$bandA], array_column($bandPairs, 'name')) === FALSE)) {
                $bandPairs[] = ['name' => $bands[$bandA].','.$bands[$bandB], 'count' => rand(50,60)];
            }
        }

        $_bandPairs = $bandPairs;

        if (($handle = @fopen(dirname(__DIR__).'/tests/generated.csv', "w")) !== FALSE) {

            while(count($bandPairs)) {
                $lineParts = [];
                $pairAdded = false;
                while ($rand = rand(0, 3)) {
                    if ($rand % 2 && !$pairAdded) {

                        if (count($bandPairs)) {
                            $lineParts[] = $bandPairs[$bP = rand(0, count($bandPairs)-1)]['name'];
                            $bandPairs[$bP]['count']--;
                            if (!$bandPairs[$bP]['count']) {
                                unset($bandPairs[$bP]);
                                $bandPairs = array_values($bandPairs);
                            }

                            $pairAdded = true;
                        }

                    } elseif($rand % 3 && count($bandPairs) && count($singleBands) && !$pairAdded) {

                        $lineParts[] = str_replace(',', ','.$singleBands[$sB = rand(0, count($singleBands)-1)]['name'].',', $bandPairs[$bP = rand(0, count($bandPairs)-1)]['name']);
                        $singleBands[$sB]['count']--;
                        if (!$singleBands[$sB]['count']) {
                            unset($singleBands[$sB]);
                            $singleBands = array_values($singleBands);
                        }

                        $bandPairs[$bP]['count']--;
                        if (!$bandPairs[$bP]['count']) {
                            unset($bandPairs[$bP]);
                            $bandPairs = array_values($bandPairs);
                        }

                        $pairAdded = true;
                    } else {

                        if (false && count($singleBands)) {
                            $lineParts[] = $singleBands[$sB = rand(0, count($singleBands)-1)]['name'];
                            $singleBands[$sB]['count']--;
                            if (!$singleBands[$sB]['count']) {
                                unset($singleBands[$sB]);
                                $singleBands = array_values($singleBands);
                            }
                        }

                    }
                }
                @fwrite($handle, implode(',', $lineParts) . "\n");
            }
            fclose($handle);
        }
        return $_bandPairs;
    }
}