<?php
$loader = require __DIR__ . '/vendor/autoload.php';
$loader->add('philwc', __DIR__ . '/src');
$change = new \philwc\Web\AddChange();

if (isset($_POST['submit'])) {
    $commitOk = true;
    foreach ($change->getFields() as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $methodName = 'set' . ucwords($field);
            $change->$methodName($_POST[$field]);
        } else {
            $commitOk = false;
        }
    }
    if ($commitOk) {
        $change->commit();
        echo 'Change Committed';
        echo '<a href="' . $_SERVER['PHP_SELF'] . '">Back</a>';
    } else {
        echo 'Required Fields Not Completed';
    }
} else {
    echo $change->getHtml($_SERVER['PHP_SELF']);
}

