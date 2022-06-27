<?php
/**
 * Gets all servers
 *
 * @return array
 */
function get_login_servers(): array
{
    $username = 'moodle';
    $password = 'm@0dl3ing';
    $defaultdatabase = 'moodle';
    $defaultlabel = 'Moodle';
    return [
        'db-pg' => array(
            // Required parameters
            'username'  => $username,
            'pass'      => $password,
            // Optional parameters
            'driver'    => 'pgsql',     // if omitted, defaults to 'server'
            'label'     => 'PosgreSQL',
            'databases' => array(
                'moodlewp' => 'Workplace',
                'moodlelms' => 'LMS',
                'phpunit' => 'PHPUnit',
                'behat' => 'Behat'
            )
        ),
        'db-mysql' => array(
            // Required parameters
            'username'  => $username,
            'pass'      => $password,
            // Optional parameters
            'label'     => 'MySQL',
            'databases' => array(
                $defaultdatabase => $defaultlabel,
            )
        ),
        'db-oracle' => array(
            // Required parameters
            'username'  => $username,
            'pass'      => $password,
            // Optional parameters
            'driver'    => 'oracle',
            'label'     => 'Oracle',
            'databases' => array(
                $defaultdatabase => $defaultlabel,
            )
        ),
        'db-mssql' => array(
            // Required parameters
            'username'  => $username,
            'pass'      => $password,
            // Optional parameters
            'driver'    => 'mssql',
            'label'     => 'MicrosoftSQL',
            'databases' => array(
                $defaultdatabase => $defaultlabel,
            )
        ),
    ];
}

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
        new OneClickLogin(get_login_servers()),
    ];
    
    return new AdminerPlugin($plugins);
}

// include original Adminer or Adminer Editor
include "./adminer.php";
?>