<?php

$changes = array();

foreach (new DirectoryIterator('sql') as $fileInfo) {
    if($fileInfo->isDot()) continue;
    $hash = str_replace('.sql', '', $fileInfo->getFilename());
    $line = '';
    $file = file($fileInfo->getPathname());
    $change = array();
    foreach($file as $line){
        if (trim($line) == '*/'){
            break;
        }
        
        $line = trim(str_replace('*', '', $line));
        
        switch(substr($line, 0, strpos($line,' '))){
            case '@date':
                $change['date'] = new DateTime(trim(str_replace('@date', '', $line)));
                break;
            case '@author':
                $change['author'] = trim(str_replace('@author', '', $line));
                break;
            case '@description':
                $change['description'] = trim(str_replace('@description', '', $line));
                break;
            default;
        }
    }
    
    $changes[$hash] = $change;
}

uasort($changes, function($a, $b) {
  $ad = $a['date'];
  $bd = $b['date'];
  
  if ($a == $b) {
    return 0;
  }

  return $a > $b ? 1 : -1;
});


var_dump($changes);

/**
 * We now select where we're up to from the db, and discard any changes previous to the current revision (including current rev).
 * 
 * After that, we should be left with a list of hashes and dates that we can incrementally add to the db by loading the relevant
 * file and executing it
 */
