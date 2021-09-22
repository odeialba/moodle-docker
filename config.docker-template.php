<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$type = 'lms';

// Prevent JS caching
$CFG->cachejs = false; // NOT FOR PRODUCTION SERVERS!
// Prevent Template caching
$CFG->cachetemplates = false; // NOT FOR PRODUCTION SERVERS!
// Prevent theme caching
$CFG->themedesignermode = true; // NOT FOR PRODUCTION SERVERS!
// Prevent sending of emails
//$CFG->noemailever = true;    // NOT FOR PRODUCTION SERVERS!
// Enable mailhog
$CFG->smtphosts = 'mailhog:1025';
$CFG->noreplyaddress = 'noreply@example.com';

$host = 'localhost';
if (!empty(getenv('MOODLE_DOCKER_WEB_HOST'))) {
    $host = getenv('MOODLE_DOCKER_WEB_HOST');
}
$port = getenv('MOODLE_DOCKER_WEB_PORT');
$portstring = '';
if (!empty($port)) {
    // Extract port in case the format is bind_ip:port.
    $parts = explode(':', $port);
    $port = end($parts);
    if ((string)(int)$port === (string)$port && (int) $port !== 80) { // Only if it's int value.
        $portstring = ":{$port}";
    }
}

//$CFG->wwwroot   = "http://{$host}{$portstring}/{$type}";
$CFG->wwwroot   = "http://{$type}.{$host}{$portstring}";

function getversion() {
    define('MATURITY_ALPHA', 50);
    define('MATURITY_BETA', 100);
    define('MATURITY_RC', 150);
    define('MATURITY_STABLE', 200);
    define('ANY_VERSION', 'any');
    define('MOODLE_INTERNAL', true);

    require(__DIR__ . '/version.php');
    $version = $branch;

    //pecl install -f runkit7
    //You should add "extension=runkit7.so" to php.ini
    //echo "extension=runkit7.so" > /usr/local/etc/php/conf.d/docker-php-runkit7.ini
    runkit7_constant_remove('MATURITY_ALPHA');
    runkit7_constant_remove('MATURITY_BETA');
    runkit7_constant_remove('MATURITY_RC');
    runkit7_constant_remove('MATURITY_STABLE');
    runkit7_constant_remove('ANY_VERSION');
    //runkit7_constant_remove('MOODLE_INTERNAL');

    return $version;
}

$version = getversion();

$CFG->dataroot = '/var/www/' . $type . '/moodledata/' . $version;
$CFG->phpunit_dataroot = '/var/www/' . $type . '/phpunitdata/' . $version;
$CFG->behat_dataroot = '/var/www/' . $type . '/behatdata/' . $version;
$CFG->prefix = 'm' . $type . $version . '_';
$CFG->phpunit_prefix = 't' . $type . $version . '_';
$CFG->behat_prefix = 'b' . $type . $version . '_';

$CFG->dbtype    = getenv('MOODLE_DOCKER_DBTYPE');
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'db';
$CFG->dbuser    = getenv('MOODLE_DOCKER_DBUSER');
$CFG->dbpass    = getenv('MOODLE_DOCKER_DBPASS');
$CFG->dbname    = getenv('MOODLE_DOCKER_DBNAME_' . strtoupper($type)) ?: getenv('MOODLE_DOCKER_DBNAME');
$CFG->dboptions = ['dbcollation' => getenv('MOODLE_DOCKER_DBCOLLATION')];

$CFG->phpunit_dbname = getenv('MOODLE_DOCKER_DBNAME_PHPUNIT');
define('TEST_EXTERNAL_FILES_HTTP_URL', 'http://exttests/' . $type);

$CFG->behat_wwwroot   = 'http://webserver/' . $type;
$CFG->behat_dbname = getenv('MOODLE_DOCKER_DBNAME_BEHAT');
$CFG->behat_profiles = array(
        'default' => array(
                'browser' => getenv('MOODLE_DOCKER_BROWSER'),
                'wd_host' => 'http://selenium:4444/wd/hub',
        ),
);
$CFG->behat_faildump_path = '/var/www/behatfaildumps';

$CFG->directorypermissions = 02777;
$CFG->admin = 'admin';

// Debug options - possible to be controlled by flag in future..
$CFG->debug = (E_ALL | E_STRICT); // DEBUG_DEVELOPER
$CFG->debugdisplay = 1;
$CFG->debugstringids = 1; // Add strings=1 to url to get string ids.
$CFG->perfdebug = 15;
$CFG->debugpageinfo = 1;
$CFG->allowthemechangeonurl = 1;
$CFG->passwordpolicy = 0;
$CFG->cronclionly = 0;
$CFG->pathtophp = '/usr/local/bin/php';

define('PHPUNIT_LONGTEST', true);

if (getenv('MOODLE_DOCKER_APP')) {
    $appport = getenv('MOODLE_DOCKER_APP_PORT') ?: 8100;

    $CFG->behat_ionic_wwwroot = "http://moodleapp:$appport";
}

if (getenv('MOODLE_DOCKER_PHPUNIT_EXTRAS')) {
    define('TEST_SEARCH_SOLR_HOSTNAME', 'solr');
    define('TEST_SEARCH_SOLR_INDEXNAME', 'test');
    define('TEST_SEARCH_SOLR_PORT', 8983);

    define('TEST_SESSION_REDIS_HOST', 'redis');
    define('TEST_CACHESTORE_REDIS_TESTSERVERS', 'redis');

    define('TEST_CACHESTORE_MONGODB_TESTSERVER', 'mongodb://mongo:27017');

    define('TEST_CACHESTORE_MEMCACHED_TESTSERVERS', "memcached0:11211\nmemcached1:11211");
    define('TEST_CACHESTORE_MEMCACHE_TESTSERVERS', "memcached0:11211\nmemcached1:11211");

    define('TEST_LDAPLIB_HOST_URL', 'ldap://ldap');
    define('TEST_LDAPLIB_BIND_DN', 'cn=admin,dc=openstack,dc=org');
    define('TEST_LDAPLIB_BIND_PW', 'password');
    define('TEST_LDAPLIB_DOMAIN', 'ou=Users,dc=openstack,dc=org');

    define('TEST_AUTH_LDAP_HOST_URL', 'ldap://ldap');
    define('TEST_AUTH_LDAP_BIND_DN', 'cn=admin,dc=openstack,dc=org');
    define('TEST_AUTH_LDAP_BIND_PW', 'password');
    define('TEST_AUTH_LDAP_DOMAIN', 'ou=Users,dc=openstack,dc=org');

    define('TEST_ENROL_LDAP_HOST_URL', 'ldap://ldap');
    define('TEST_ENROL_LDAP_BIND_DN', 'cn=admin,dc=openstack,dc=org');
    define('TEST_ENROL_LDAP_BIND_PW', 'password');
    define('TEST_ENROL_LDAP_DOMAIN', 'ou=Users,dc=openstack,dc=org');
}

require_once(__DIR__ . '/lib/setup.php');
