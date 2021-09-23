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
        new AdminerSimpleMenu(true, false),
        new AdminerTableHeaderScroll(),
        new AdminerTablesFilter(),
    ];
    
    return new AdminerPlugin($plugins);
}

// include original Adminer or Adminer Editor
include "./adminer.php";
?>