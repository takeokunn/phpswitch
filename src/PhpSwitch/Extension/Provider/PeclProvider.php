<?php

namespace PhpSwitch\Extension\Provider;

use CLIFramework\Logger;
use Exception;
use GetOptionKit\OptionResult;
use PEARX\Channel as PeclChannel;
use PhpSwitch\Downloader\DownloadFactory;

class PeclProvider implements Provider
{
    public $site = 'pecl.php.net';
    public $owner = null;
    public $repository = null;
    public $packageName = null;
    public $defaultVersion = 'stable';

    public function __construct(private readonly Logger $logger, private readonly OptionResult $optionResult)
    {
    }

    public static function getName()
    {
        return 'pecl';
    }

    public function buildPackageDownloadUrl($version = 'stable')
    {
        $packageName = $this->getPackageName();

        if ($packageName === null) {
            throw new Exception('Repository invalid.');
        }

        $channel = new PeclChannel($this->site);
        $restBaseUrl = $channel->getRestBaseUrl();
        $url = "$restBaseUrl/r/" . strtolower((string) $packageName);

        $baseDownloader = DownloadFactory::getInstance($this->logger, $this->optionResult);

        // translate version name into numbers
        if (in_array($version, ['stable', 'latest', 'beta'])) {
            $stabilityTxtUrl = $url . '/' . $version . '.txt';
            if ($ret = $baseDownloader->request($stabilityTxtUrl)) {
                $version = (string) $ret;
            } else {
                throw new Exception("Can not translate stability {$version} into exact version name.");
            }
        }

        $xmlUrl = $url . '/' . $version . '.xml';
        $ret = $baseDownloader->request($xmlUrl);

        if ($ret === false) {
            throw new Exception('Unable to fetch package xml');
        }

        $xml = simplexml_load_string($ret);

        if ($xml === false) {
            throw new Exception("Error in XMl document: $url");
        }

        // just use tgz format file.
        return $xml->g . '.tgz';
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    public function getPackageName()
    {
        return $this->packageName;
    }

    public function setPackageName($packageName)
    {
        $this->packageName = $packageName;
    }

    public function exists($url, $packageName = null)
    {
        $this->setOwner(null);
        $this->setRepository(null);
        $this->setPackageName($url);

        return true;
    }

    public function isBundled($name)
    {
        return in_array(strtolower((string) $name), ['bcmath', 'bz2', 'calendar', 'com_dotnet', 'ctype', 'curl', 'date', 'dba', 'dom', 'enchant', 'exif', 'fileinfo', 'filter', 'ftp', 'gd', 'gettext', 'gmp', 'hash', 'iconv', 'imap', 'interbase', 'intl', 'json', 'ldap', 'libxml', 'mbstring', 'mcrypt', 'mssql', 'mysqli', 'mysqlnd', 'oci8', 'odbc', 'opcache', 'openssl', 'pcntl', 'pcre', 'pdo', 'pdo_dblib', 'pdo_firebird', 'pdo_mysql', 'pdo_oci', 'pdo_odbc', 'pdo_pgsql', 'pdo_sqlite', 'pgsql', 'phar', 'posix', 'pspell', 'readline', 'recode', 'reflection', 'session', 'shmop', 'simplexml', 'skeleton', 'snmp', 'soap', 'sockets', 'spl', 'sqlite3', 'standard', 'sysvmsg', 'sysvsem', 'sysvshm', 'tidy', 'tokenizer', 'wddx', 'xml', 'xmlreader', 'xmlrpc', 'xmlwriter', 'xsl', 'zip', 'zlib', 'ext_skel', 'ext_skel_win32']);
    }

    public function buildKnownReleasesUrl()
    {
        return sprintf('https://pecl.php.net/rest/r/%s/allreleases.xml', $this->getPackageName());
    }

    public function parseKnownReleasesResponse($content)
    {
        $xml = simplexml_load_string((string) $content);
        $releases = [];

        foreach ($xml->r as $r) {
            $releases[] = (string) $r->v;
        }

        return $releases;
    }

    public function getDefaultVersion()
    {
        return $this->defaultVersion;
    }

    public function setDefaultVersion($version)
    {
        $this->defaultVersion = $version;
    }

    public function shouldLookupRecursive()
    {
        return false;
    }

    public function resolveDownloadFileName($version)
    {
        $url = $this->buildPackageDownloadUrl($version);
        // Check if the url is for php source archive
        if (preg_match('/php-.+\.tar\.(bz2|gz|xz)/', (string) $url, $parts)) {
            return $parts[0];
        }

        // try to get the filename through parse_url
        $path = parse_url((string) $url, PHP_URL_PATH);
        if (false === $path || !str_contains($path, '.')) {
            return;
        }

        return basename($path);
    }

    public function extractPackageCommands($currentPhpExtensionDirectory, $targetFilePath)
    {
        $cmds = ["tar -C $currentPhpExtensionDirectory -xzf $targetFilePath"];

        return $cmds;
    }

    public function postExtractPackageCommands($currentPhpExtensionDirectory, $targetFilePath)
    {
        $targetPkgDir = $currentPhpExtensionDirectory . DIRECTORY_SEPARATOR . $this->getPackageName();
        $info = pathinfo((string) $targetFilePath);
        $packageName = $this->getPackageName();

        $cmds = [
            "rm -rf $targetPkgDir",
            // Move "memcached-2.2.7" to "memcached"
            "mv $currentPhpExtensionDirectory/{$info['filename']} $currentPhpExtensionDirectory/$packageName",
            // Move "ext/package.xml" to "memcached/package.xml"
            "mv $currentPhpExtensionDirectory/package.xml $currentPhpExtensionDirectory/$packageName/package.xml",
        ];

        return $cmds;
    }
}
