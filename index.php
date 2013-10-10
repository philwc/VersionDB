<?php

var_dump($_POST);
if(isset($_POST['submit'])){
    $up = $_POST['up'];
    $user = $_POST['user'];
    $description = $_POST['description'];
    
    $today = new \DateTime();
    $header = <<< EOF
/**
 * This is an automatically generated file. Please do not edit.
 * @date {$today->format('Y-m-d H:i:s')}
 * @author $user
 * @description $description
 */


EOF;
    
   
    $up = $header. $up;
    var_dump(sha1($up));
    var_dump($up);
    
    file_put_contents('sql/'.sha1($up).'.sql', $up);
    echo 'Saved';

}else{
    echo <<< EOF
        <form action="" method="POST">
            <label for="up">Up: </label>
            <textarea name="up"></textarea>
            <label for="down">Down: </label>
            <textarea name="down"></textarea>
            <label for="user">User: </label>
            <input type="text" name="user">
            <label for="description">Description: </label>
            <textarea name="description"></textarea>
            <input type="submit" name="submit">
        </form>
EOF;

}