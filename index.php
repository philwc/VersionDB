<?php
$loader = require __DIR__ . '/vendor/autoload.php';
$loader->add('philwc', __DIR__ . '/src');
$change = new \philwc\Web\AddChange();
$fields = $change->getFields();

if(isset($_POST['submit'])){
    foreach($fields as $field){
        if(isset($_POST[$field])){
            $methodName = 'set'.ucwords($field);
            $change->$methodName($_POST[$field]);
        }
    }
    $change->commit();
    echo 'Change Committed';
    echo '<a href="'.$_SERVER['PHP_SELF'].'">Back</a>';
}else{
    echo $change->getHtml($_SERVER['PHP_SELF']);
}

