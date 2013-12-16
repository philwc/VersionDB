<?php
/**
 * @author Philip Wright- Christie <pwrightchristie.sfp@gmail.com>
 * Date: 12/12/13
 */

namespace philwc\Web;
use philwc\Classes;

class AddChange {

    private $updateScript;
    private $downgradeScript;
    private $author;
    private $description;

    /**
     * @param $author
     *
     * @return $this
     */
    public function setAuthor( $author )
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @param $description
     *
     * @return $this
     */
    public function setDescription( $description )
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param $downgradeScript
     *
     * @return $this
     */
    public function setDowngradeScript( $downgradeScript )
    {
        $this->downgradeScript = $downgradeScript;

        return $this;
    }

    /**
     * @param $updateScript
     *
     * @return $this
     */
    public function setUpdateScript( $updateScript )
    {
        $this->updateScript = $updateScript;

        return $this;
    }

    /**
     * Get Fields
     *
     * @return array
     */
    public function getFields(){
        return array_keys(get_object_vars($this));
    }

    /**
     * Commit
     *
     * @throws \Exception
     */
    public function commit(){
        foreach(array('updateScript', 'downgradeScript', 'author', 'description') as $field){
            if(!isset($this->$field)){
                throw new \Exception('Required Field '.$field.' Not Set');
            }
        }

        $config = new \philwc\Classes\Config();
        $sqlDir = $config->getSetting('file', 'sqlDir');

        $filesystemUpdates = new \philwc\Classes\FilesystemUpdates($sqlDir);
        $filesystemUpdates->addFiles($this->updateScript, $this->downgradeScript, $this->author, $this->description);
    }

    /**
     * Get HTML
     * @param $action
     *
     * @return string
     */
    public function getHtml($action){
        $html = '<form action="'.$action.'" method="POST">';
        foreach($this->getFields() as $field){
            $a             = preg_split('/(?<=[a-z])(?=[A-Z])/x', $field);
            $fieldTitle = ucwords(implode(' ', $a));
            $html .= '<label for="'.$field.'">'.$fieldTitle.': </label><input type="text" name="'.$field.'" id="'.$field.'"/>';
        }
        $html .= '<input name="submit" type="submit"></form>';

        return $html;
    }
}