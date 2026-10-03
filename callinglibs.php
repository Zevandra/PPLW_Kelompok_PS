<?php
// this is our own 'bootstrap yeah' 
spl_autoload_register(function (string $class_name) {
    $filename = strtolower($class_name) . ".php";

    foreach (['dbconnection', 'generallogic'] as $subdir) {
        $file = __DIR__ . "/logicgates/" . $subdir . "/" . $filename;
        if (file_exists($file)) {
            include_once $file;
            return;
        }
    }
});