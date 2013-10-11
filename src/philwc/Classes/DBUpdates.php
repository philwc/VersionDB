<?php

namespace philwc\Classes;

/**
 * Description of DBUpdates
 *
 * @author Phil Wright- Christie <pwrightchristie.sfp@gmail.com>
 */
class DBUpdates
{

    private $db;
    private $changelogTable;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = new \philwc\Classes\DB();

        $config = new \philwc\Classes\Config();

        $this->changelogTable = $config->getSetting('database', 'changelogtable');

        $this->checkCreateTable();
    }

    /**
     * Check if the changelog table exists, create if not.
     * @return boolean
     */
    private function checkCreateTable()
    {
        $this->db->setSql('SELECT 1 FROM ' . $this->changelogTable . ' LIMIT 1');

        if ($this->db->run() === false) {
            $this->db->setSql('CREATE TABLE `' . $this->changelogTable . '` (`id` varchar(255) DEFAULT NULL, `date` datetime DEFAULT NULL,
                `author` varchar(255) DEFAULT NULL, `description` text, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=latin1');

            return $this->db->run() !== false;
        } else {
            return true;
        }
    }

    /**
     * Add Change Record
     * @param string    $hash
     * @param \DateTime $date
     * @param string    $author
     * @param string    $description
     *
     * @return boolean
     */
    private function addRecord($hash, \DateTime $date, $author, $description)
    {
        $result = $this->db
            ->setSql('SELECT id FROM ' . $this->changelogTable . ' WHERE id=:hash')
            ->setParameter(
                array(
                    'hash' => $hash
                )
            )
            ->run();

        if ($result->rowCount() == 0) {
            $this->db
                ->setSql('INSERT INTO ' . $this->changelogTable . ' VALUES (:hash, :date, :author, :description)')
                ->setParameter(array(
                    'hash'        => $hash,
                    'date'        => $date->format('Y-m-d H:i:s'),
                    'author'      => $author,
                    'description' => $description,
            ));

            return $this->db->run() !== false;
        } else {
            return 'Changelog Record already exists';
        }
    }

    /**
     * Apply Script
     * @param string $hash
     * @param array  $details
     *
     * @return boolean
     * @throws \Exception
     */
    public function applyScript($hash, array $details)
    {
        $sql = file_get_contents($details['file']);

        $result = $this->db->setSql($sql)->run();

        if ($result !== false) {
            return $this->addRecord($hash, $details['date'], $details['author'], $details['description']);
        } else {
            throw new \Exception('Failed applying update script: ' . "\n" . $details['file'] . "\n\n" . $this->db->getLastError());
        }
    }

    /**
     * Get Existing Hashes
     * @return array
     */
    public function getExistingHashes()
    {
        $result = $this->db
            ->setSql('SELECT id FROM ' . $this->changelogTable)
            ->run();

        $hashes = array();
        while ($row    = $result->fetch()) {
            $hashes[] = $row['id'];
        }

        return $hashes;
    }

}
