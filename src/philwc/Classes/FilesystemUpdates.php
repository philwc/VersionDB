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

    /**
     * Constructor
     * @param string $sqlDir The directory to scan for SQL files
     */
    public function __construct($sqlDir)
    {
        $this->sqlDir = $sqlDir;
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
        $path = realpath($this->sqlDir . '/' . $hash . '.sql');
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

        return $this->sortChanges($changes);
    }

    private function getHash($file)
    {
        return str_replace('.sql', '', $file->getFilename());
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
            ->name('*.sql')
            ->in($sqlDir);

        return $iterator;
    }

    private function sortChanges($changes)
    {
        uasort($changes,
            function($a, $b) {
                $ad = $a['date'];
                $bd = $b['date'];

                if ($ad == $bd) {
                    return 0;
                }

                return $ad > $bd ? 1 : -1;
            });

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
            /**
             * Extract the data
             * @todo make this a bit smarter
             */
            switch (substr($line, 0, strpos($line, ' '))) {
                case '@date':
                    $change['date']        = new \DateTime(trim(str_replace('@date', '', $line)));
                    break;
                case '@author':
                    $change['author']      = trim(str_replace('@author', '', $line));
                    break;
                case '@description':
                    $change['description'] = trim(str_replace('@description', '', $line));
                    break;
                default;
            }
        }

        return $change;
    }

}

