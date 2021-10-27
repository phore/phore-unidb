<?php


namespace Phore\UniDb\Cli;

use Phore\UniDb\Helper\OneFile;
use Phore\UniDb\UniDbConfig;

/**
 * Class UniDbCli
 * @package Phore\UniDb\Cli
 * @internal
 */
class UniDbCli
{

    private function showHelp()
    {
        echo file_get_contents(__DIR__ . "/usage.txt");
    }


    private function export (string $target)
    {
        if ( ! is_dir($target))
            throw new \InvalidArgumentException("Export target dir is not existing: $target");
        $unidb = UniDbConfig::get();
        $result = $unidb->export(path:$target);
        print_r($result);
    }


    private function import(string $file)
    {
        $unidb = UniDbConfig::get();
        $strategy = UniDbConfig::getStrategy();
        if ($strategy === null)
            throw new \InvalidArgumentException("No default strategy defined");

        if (is_dir($file)) {
            $result = $unidb->import(path: $file);
        }
        print_r ($result);
    }

    public function main($argv, $argc)
    {
        array_shift($argv);
        while ($p = array_shift($argv)) {
            switch ($p) {
                case "help":
                case "-h":
                    $this->showHelp();
                    exit;

                case "-i":
                    require_once array_shift($argv);
                    break;

                case "export":
                    $this->export(array_shift($argv));
                    break;

                case "import":
                    $this->import(array_shift($argv));
                    break;

            }
        }
    }
}