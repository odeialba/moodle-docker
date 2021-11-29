<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$type = basename(dirname(__FILE__));

// Prevent JS caching
// $CFG->cachejs = false; // NOT FOR PRODUCTION SERVERS!
// Prevent Template caching
// $CFG->cachetemplates = false; // NOT FOR PRODUCTION SERVERS!
// Prevent theme caching
// $CFG->themedesignermode = true; // NOT FOR PRODUCTION SERVERS!
// Prevent sending of emails
//$CFG->noemailever = true; // NOT FOR PRODUCTION SERVERS!
// Enable mailhog
$CFG->smtphosts = 'mailhog:1025';
$CFG->noreplyaddress = 'noreply@example.com';

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

$CFG->dataroot = '/var/www/data/' . $type . '/moodledata/' . $version;
$CFG->phpunit_dataroot = '/var/www/data/' . $type . '/phpunitdata/' . $version;
$CFG->behat_dataroot = '/var/www/data/' . $type . '/behatdata/' . $version;

if (!file_exists($CFG->dataroot)) {
    mkdir($CFG->dataroot, 0777, true);
}
if (!file_exists($CFG->phpunit_dataroot)) {
    mkdir($CFG->phpunit_dataroot, 0777, true);
}
if (!file_exists($CFG->behat_dataroot)) {
    mkdir($CFG->behat_dataroot, 0777, true);
}

$CFG->prefix = 'm' . $type . $version . '_';
$CFG->phpunit_prefix = 't' . $type . $version . '_';
$CFG->behat_prefix = 'b' . $type . $version . '_';

// Maximum length for table prefixes in oracle is 2 characters.
if (getenv('MOODLE_DOCKER_DBHOST') === 'db-oracle') {
    if ($type === 'lms') {
        switch ($version) {
            case '41':
            case '401':
                $CFG->prefix = 'a_';
                $CFG->phpunit_prefix = 'b_';
                $CFG->behat_prefix = 'c_';
                break;
            case '400':
                $CFG->prefix = 'd_';
                $CFG->phpunit_prefix = 'e_';
                $CFG->behat_prefix = 'f_';
                break;
            case '311':
                $CFG->prefix = 'g_';
                $CFG->phpunit_prefix = 'h_';
                $CFG->behat_prefix = 'i_';
                break;
            case '310':
                $CFG->prefix = 'j_';
                $CFG->phpunit_prefix = 'k_';
                $CFG->behat_prefix = 'l_';
                break;
            default:
                $CFG->prefix = 'm_';
                $CFG->phpunit_prefix = 'n_';
                $CFG->behat_prefix = 'o_';
        }
    } else if ($type === 'wp') {
        switch ($version) {
            case '400':
                $CFG->prefix = 'p_';
                $CFG->phpunit_prefix = 'q_';
                $CFG->behat_prefix = 'r_';
                break;
            case '311':
                $CFG->prefix = 's_';
                $CFG->phpunit_prefix = 't_';
                $CFG->behat_prefix = 'u_';
                break;
            default:
                $CFG->prefix = 'v_';
                $CFG->phpunit_prefix = 'w_';
                $CFG->behat_prefix = 'x_';
        }
    } else {
        switch ($version) {
            case '41':
                $CFG->prefix = 'y_';
                $CFG->phpunit_prefix = 'z_';
                $CFG->behat_prefix = '0_';
                break;
            case '400':
                $CFG->prefix = '1_';
                $CFG->phpunit_prefix = '2_';
                $CFG->behat_prefix = '3_';
                break;
            case '311':
                $CFG->prefix = '4_';
                $CFG->phpunit_prefix = '5_';
                $CFG->behat_prefix = '6_';
                break;
            default:
                $CFG->prefix = '7_';
                $CFG->phpunit_prefix = '8_';
                $CFG->behat_prefix = '9_';
        }
    }
}

$CFG->dbtype    = getenv('MOODLE_DOCKER_DBTYPE');
$CFG->dblibrary = 'native';
$CFG->dbhost    = getenv('MOODLE_DOCKER_DBHOST');
$CFG->dbuser    = getenv('MOODLE_DOCKER_DBUSER');
$CFG->dbpass    = getenv('MOODLE_DOCKER_DBPASS');
$CFG->dbname    = getenv('MOODLE_DOCKER_DBNAME_' . strtoupper($type)) ?: getenv('MOODLE_DOCKER_DBNAME');
$CFG->dboptions = ['dbcollation' => getenv('MOODLE_DOCKER_DBCOLLATION')];

$CFG->phpunit_dbname = getenv('MOODLE_DOCKER_DBNAME_PHPUNIT') ?: getenv('MOODLE_DOCKER_DBNAME');
define('TEST_EXTERNAL_FILES_HTTP_URL', 'http://exttests/' . $type);

$CFG->behat_wwwroot   = 'http://webserver/' . $type;
$CFG->behat_dbname = getenv('MOODLE_DOCKER_DBNAME_BEHAT') ?: getenv('MOODLE_DOCKER_DBNAME');
$CFG->behat_profiles = array(
        'default' => array(
                'browser' => getenv('MOODLE_DOCKER_BROWSER'),
                'wd_host' => 'http://selenium:4444/wd/hub',
        ),
);
$CFG->behat_faildump_path = '/var/www/behatfaildumps';

$CFG->directorypermissions = 02777;
$CFG->admin = 'admin';

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
