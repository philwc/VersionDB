<?php

namespace philwc\Classes;

use Symfony\Component\Yaml\Yaml;

/**
 * Description of Config
 *
 * @author Phil Wright- Christie <pwrightchristie.sfp@gmail.com>
 */
class Config
{

    private $data;

    public function __construct()
    {
        $this->loadSettings();
    }

    public function getSetting($section, $value)
    {
        if (isset($this->data[$section])) {
            if (isset($this->data[$section][$value])) {
                return $this->data[$section][$value];
            } else {
                throw new \Exception('Invalid Value: ' . $value);
            }
        } else {
            throw new \Exception('Invalid Section: ' . $section);
        }
    }

    private function loadSettings()
    {
        $settingsFilename = $this->findSettings();
        $this->data       = Yaml::parse(file_get_contents($settingsFilename));
    }

    /**
     * Find Settings File
     * @param string $currentDir
     * @param string $search
     *
     * @return string
     */
    private function findSettings($currentDir = __DIR__, $search = 'settings.yml')
    {
        $search = '/' . $search;

        if (!file_exists($currentDir . $search)) {
            while (strpos(substr($currentDir, 1), DIRECTORY_SEPARATOR) !== false) {
                $currentDir = substr($currentDir, 0, strrpos($currentDir, DIRECTORY_SEPARATOR));

                if (file_exists($currentDir . $search)) {
                    return $currentDir . $search;
                }
            }

            throw new \Exception('Unable to locate settings.yml file');
        } else {
            return $currentDir . $search;
        }
    }

}
