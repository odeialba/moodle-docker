<?php
function adminer_object() {
    // required to run any plugin
    include_once "./plugins/plugin.php";
    
    // autoloader
    foreach (glob("plugins/*.php") as $filename) {
        include_once "./$filename";
    }
    
    $plugins = [
        // specify enabled plugins here
        new AdminerJsonPreview(),
        new AdminerResize(),
        new AdminerTableHeaderScroll(),
        new AdminerTablesFilter(),
        new AdminerSimpleMenu(true, false),
    ];
    
    return new AdminerPlugin($plugins);
}

// include original Adminer or Adminer Editor
include "./adminer.php";
?>