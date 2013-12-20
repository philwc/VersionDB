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
     *
     * @return boolean
     */
    private function checkCreateTable()
    {
        $this->db->setSql('SELECT 1 FROM ' . $this->changelogTable . ' LIMIT 1');

        if ($this->db->run() === false) {
            $this->db->setSql(
                     'CREATE TABLE `' . $this->changelogTable . '` (`id` varchar(255) DEFAULT NULL, `down` varchar(255) DEFAULT NULL, `date` datetime DEFAULT NULL,
                `author` varchar(255) DEFAULT NULL, `description` text, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=latin1'
            );

            return $this->db->run() !== false;
        } else {
            return true;
        }
    }

    /**
     * Add Change Record
     *
     * @param string    $hash
     * @param string    $down
     * @param \DateTime $date
     * @param string    $author
     * @param string    $description
     *
     * @return boolean
     */
    private function addRecord( $hash, $down, \DateTime $date, $author, $description )
    {
        $result = $this->db->setSql('SELECT id FROM ' . $this->changelogTable . ' WHERE id=:hash')
                           ->setParameter(
                           array(
                                'hash' => $hash
                           )
            )
                           ->run();

        if ($result === false || $result->rowCount() == 0) {
            $this->db->setSql(
                     'INSERT INTO ' . $this->changelogTable . ' VALUES (:hash, :down, :date, :author, :description)'
                )
                     ->setParameter(
                     array(
                          'hash'        => $hash,
                          'down'        => $down,
                          'date'        => $date->format('Y-m-d H:i:s'),
                          'author'      => $author,
                          'description' => $description,
                     )
                );

            return $this->db->run() !== false;
        } else {
            return 'Changelog Record already exists';
        }
    }

    /**
     * Remove Record
     *
     * @param type $hash
     *
     * @return type
     */
    private function removeRecord( $hash )
    {
        $this->db->setSql('DELETE FROM ' . $this->changelogTable . ' WHERE id=:hash OR down=:hash')
                 ->setParameter(
                 array(
                      'hash' => $hash
                 )
            );

        return $this->db->run() !== false;
    }

    /**
     * Apply Script
     *
     * @param string $hash
     * @param string $down
     * @param array  $details
     *
     * @return boolean
     * @throws \Exception
     */
    public function applyScript( $hash, $down, array $details )
    {
        $sql = file_get_contents($details['file']);

        $result = $this->db->setSql($sql)
                           ->run();

        if ($result !== false) {
            $this->updateRecord($down, $hash, $details);
        } else {

            $nativeResult = $this->db->runNative();
            if ($nativeResult == 0) {
                $this->updateRecord($down, $hash, $details);
            } else {
                throw new \Exception('Failed applying update script: ' . "\n" . $details['file'] . "\n\n" . $sql . "\n\n" . $this->db->getLastError(
                    ));
            }
        }
    }

    /**
     * Update Record
     *
     * @param string $down
     * @param string $hash
     * @param array  $details
     *
     * @return bool
     */
    private function updateRecord( $down, $hash, array $details )
    {
        if ($down === true) {
            return $this->removeRecord($hash);
        } else {
            return $this->addRecord(
                        $hash,
                            $down,
                            $details['date'],
                            $details['author'],
                            $details['description']
                );
        }
    }

    /**
     * Get Existing Hashes
     *
     * @param boolean $small
     *
     * @return array
     */
    public function getExistingHashes( $small = true )
    {
        if ($small) {
            $result = $this->db->setSql('SELECT id FROM ' . $this->changelogTable)
                               ->run();

            $hashes = array();
            while ($row = $result->fetch()) {
                $hashes[] = $row['id'];
            }
        } else {
            $result = $this->db->setSql('SELECT * FROM ' . $this->changelogTable)
                               ->run();

            $hashes = array();
            $i      = 1;
            while ($row = $result->fetch()) {
                $hash = array();
                foreach ($row as $key => $value) {
                    $hash[$key] = $value;
                }

                $hashes[] = array_merge(array('record' => $i), $hash);
                $i++;
            }
        }

        return $hashes;
    }

}
