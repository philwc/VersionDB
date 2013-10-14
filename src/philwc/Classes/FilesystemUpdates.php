<?php

namespace philwc\Classes;

use Symfony\Component\Finder\Finder;

/**
 * FilesystemUpdates
 *
 * @author Phil Wright- Christie <philwc@gmail.com>
 */
class FilesystemUpdates
{

    private $sqlDir;
    private $up;

    /**
     * Constructor
     * @param string $sqlDir The directory to scan for SQL files
     * @param string $upDown Update or downgrade script
     */
    public function __construct($sqlDir, $up = true)
    {
        $this->sqlDir = $sqlDir;
        $this->up     = $up;
    }

    /**
     * Add Files
     * @param string $up
     * @param string $down
     * @param string $author
     * @param string $description
     *
     * @throws Exception
     */
    public function addFiles($up, $down, $author, $description)
    {
        $today      = new \DateTime();
        $downHeader = <<< EOF
/**
 * This is an automatically generated file. Please do not edit.
 * @date {$today->format('Y-m-d H:i:s')}
 * @author $author
 * @description $description
 */

EOF;
        $down       = $downHeader . $down;
        $downHash   = sha1($down);
        $downName   = $this->sqlDir . '/' . $downHash . '.down.sql';
        if (!file_put_contents($downName, $down)) {
            throw new Exception('Unable to write file ' . $downName);
        }

        $upHeader = <<< EOF
/**
 * This is an automatically generated file. Please do not edit.
 * @date {$today->format('Y-m-d H:i:s')}
 * @author $author
 * @description $description
 * @down $downHash
 */

EOF;

        $up     = $upHeader . $up;
        $upName = $this->sqlDir . '/' . sha1($up) . '.up.sql';
        if (!file_put_contents($upName, $up)) {
            throw new Exception('Unable to write file ' . $upName);
        }
    }

    /**
     * Get File
     * @param string $hash
     *
     * @return string
     * @throws \Exception
     */
    public function getFile($hash)
    {
        $path = realpath($this->sqlDir . '/' . $hash . '.' . $this->getUpDownString() . '.sql');
        if (file_exists($path)) {
            return $path;
        } else {
            throw new \Exception('Invalid Hash Or Path');
        }
    }

    /**
     * Get
     *
     * @return array
     * @throws \Exception
     */
    public function get()
    {
        $changes = array();

        foreach ($this->getFiles($this->sqlDir) as $file) {

            $hash = $this->compareHash($file);

            if (is_array($hash)) {
                throw new \Exception('File hashes do not match for file ' . $file->getPathname() . ': ' . $hash['file'] . ' -- ' . $hash['name']);
            }

            $changes[$hash] = $this->processFile($file);
        }

        return $this->sortChanges($changes, $this->up);
    }

    private function getHash($file)
    {
        return str_replace(array('.up.sql', '.down.sql'), '', $file->getFilename());
    }

    private function getUpDownString()
    {
        return $this->up ? 'up' : 'down';
    }

    private function compareHash($file)
    {
        $nameHash = $this->getHash($file);

        $fileHash = sha1(file_get_contents($file->getPathname()));

        if ($nameHash === $fileHash) {

            return $nameHash;
        } else {

            return array(
                'file' => $fileHash,
                'name' => $nameHash,
            );
        }
    }

    private function getFiles($sqlDir)
    {
        $finder = new Finder();

        $iterator = $finder
            ->files()
            ->name('*.' . $this->getUpDownString() . '.sql')
            ->in($sqlDir);

        return $iterator;
    }

    /**
     * Sort Changes
     * @param array   $changes
     * @param boolean $ascending
     *
     * @return array
     */
    private function sortChanges(array $changes, $ascending = true)
    {
        if ($ascending) {
            uasort($changes,
                function($a, $b) {
                    $ad = $a['date'];
                    $bd = $b['date'];

                    if ($ad == $bd) {
                        return 0;
                    }

                    return $ad > $bd ? 1 : -1;
                });
        } else {
            uasort($changes,
                function($a, $b) {
                    $ad = $a['date'];
                    $bd = $b['date'];

                    if ($ad == $bd) {
                        return 0;
                    }

                    return $ad < $bd ? 1 : -1;
                });
        }

        return $changes;
    }

    private function processFile(\Symfony\Component\Finder\SplFileInfo $fileInfo)
    {
        $line   = '';
        $file   = file($fileInfo->getPathname());
        $change = array();
        foreach ($file as $line) {
            /*
             * If we've reached the end of the comment block, we're done
             */
            if (trim($line) == '*/') {
                break;
            }

            $line = trim(str_replace('*', '', $line));

            $change = array_merge($change, $this->extractTag($line));
        }

        return $change;
    }

    private function extractTag($line)
    {
        if (substr($line, 0, 1) === '@') {
            $tagPos = strpos($line, ' ') - 1;
            $tag    = substr($line, 1, $tagPos);
            $data   = trim(substr($line, $tagPos + 1));

            if (strpos($tag, 'date') !== false) {
                try {
                    $data = new \DateTime($data);
                } catch (Exception $e) {

                }
            }

            return array($tag => $data);
        }

        return array();
    }

}

