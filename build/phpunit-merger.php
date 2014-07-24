<?php 

$modules  = new SimpleXMLElement(file_get_contents(__DIR__.'/modules.xml'));

$fileFound = false;
foreach ($modules->modules[0] as $module) {

    $inputFile  = __DIR__.'/../../'.$module->path.'/build/junit.xml';
    //$inputFile  = __DIR__.'/'.$module->name.'/junit.xml';
    if (!file_exists($inputFile)) {
        continue;
    }
    
    if(!$fileFound){
        $mergedFile = new DOMDocument();
        $mergedFile->load($inputFile);
        
        $res = $mergedFile->getElementsByTagName('testsuites')->item(0);
        
        $fileFound = true;
        continue;
    }
    
    $doc = new DOMDocument();
    $doc->load($inputFile);
    
    $items = $doc->getElementsByTagName('testsuite');
    for ($i = 0; $i < $items->length; $i++) {
        $itemResult = $mergedFile->importNode($items->item($i), true);
        
        $res->appendChild($itemResult);
    }
}

if ($fileFound){
    $mergedFile->save(__DIR__.'/phpunit/junit.xml');    
}

