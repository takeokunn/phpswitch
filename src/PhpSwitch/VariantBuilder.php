<?php

namespace PhpSwitch;

use Exception;
use PhpSwitch\Exception\OopsException;
use PhpSwitch\PrefixFinder\BrewPrefixFinder;
use PhpSwitch\PrefixFinder\ExecutablePrefixFinder;
use PhpSwitch\PrefixFinder\IncludePrefixFinder;
use PhpSwitch\PrefixFinder\LibPrefixFinder;
use PhpSwitch\PrefixFinder\PkgConfigPrefixFinder;
use PhpSwitch\PrefixFinder\UserProvidedPrefix;

function first_existing_executable($possiblePaths)
{
    $existingPaths = array_filter(
        array_filter(
            array_filter($possiblePaths, 'file_exists'),
            'is_file'
        ),
        'is_executable'
    );

    if (!empty($existingPaths)) {
        return realpath($existingPaths[0]);
    }

    return false;
}

function exec_line($command)
{
    $output = [];
    exec($command, $output, $retval);
    if ($retval === 0) {
        $output = array_filter($output);

        return end($output);
    }

    return false;
}

/**
 * VariantBuilder build variants to `./configure' parameters.
 */
class VariantBuilder
{
    /**
     * Available variant definitions.
     *
     * @var array<string,string|array<string>|callable>
     */
    private array $variants = [];

    private array $conflicts = [
        // PHP Version lower than 5.4.0 can only built one SAPI at the same time.
        'apxs2' => ['fpm', 'cgi'],
        'editline' => ['readline'],
        'readline' => ['editline'],
        // dtrace is not compatible with phpdbg: https://github.com/krakjoe/phpdbg/issues/38
        'dtrace' => ['phpdbg'],
    ];

    /**
     * @var array is for checking built variants
     *
     * contains ['-pdo','mysql','-sqlite','-debug']
     */
    private array $builtList = [];

    public $virtualVariants = [
        'dbs' => ['sqlite', 'mysql', 'pgsql', 'pdo'],
        'mb' => ['mbstring', 'mbregex'],
        // provide no additional feature
        'neutral' => [],
        'small' => ['bz2', 'cli', 'dom', 'filter', 'ipc', 'json', 'mbregex', 'mbstring', 'pcre', 'phar', 'posix', 'readline', 'xml', 'curl', 'openssl'],
        // provide all basic features
        'default' => ['bcmath', 'bz2', 'calendar', 'cli', 'ctype', 'dom', 'fileinfo', 'filter', 'ipc', 'json', 'mbregex', 'mbstring', 'mhash', 'pcntl', 'pcre', 'pdo', 'pear', 'phar', 'posix', 'readline', 'sockets', 'tokenizer', 'xml', 'curl', 'openssl', 'zip'],
    ];

    public function __construct()
    {
        // init variant builders
        $this->variants['all'] = '--enable-all';
        $this->variants['dba'] = '--enable-dba';
        $this->variants['ipv6'] = '--enable-ipv6';
        $this->variants['dom'] = '--enable-dom';
        $this->variants['calendar'] = '--enable-calendar';
        $this->variants['wddx'] = '--enable-wddx';
        $this->variants['static'] = '--enable-static';
        $this->variants['inifile'] = '--enable-inifile';
        $this->variants['inline'] = '--enable-inline-optimization';

        $this->variants['cli'] = '--enable-cli';

        $this->variants['ftp'] = '--enable-ftp';
        $this->variants['filter'] = '--enable-filter';
        $this->variants['gcov'] = '--enable-gcov';
        $this->variants['zts'] = function (ConfigureParameters $configureParameters, Build $build) {
            if ($build->compareVersion('8.0') < 0) {
                return $configureParameters->withOption('--enable-maintainer-zts');
            }

            return $configureParameters->withOption('--enable-zts');
        };

        $this->variants['json'] = '--enable-json';
        $this->variants['hash'] = '--enable-hash';
        $this->variants['exif'] = '--enable-exif';

        $this->variants['mbstring'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $configureParameters = $configureParameters->withOption('--enable-mbstring');

            if ($build->compareVersion('7.4') >= 0 && !$build->isDisabledVariant('mbregex')) {
                $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new IncludePrefixFinder('oniguruma.h'), new BrewPrefixFinder('oniguruma')]);

                if ($prefix !== null) {
                    $configureParameters = $configureParameters->withPkgConfigPath($prefix . '/lib/pkgconfig');
                }
            }

            return $configureParameters;
        };

        $this->variants['mbregex'] = '--enable-mbregex';
        $this->variants['libgcc'] = '--enable-libgcc';

        $this->variants['pdo'] = '--enable-pdo';
        $this->variants['posix'] = '--enable-posix';
        $this->variants['embed'] = '--enable-embed';
        $this->variants['sockets'] = '--enable-sockets';
        $this->variants['debug'] = '--enable-debug';
        $this->variants['phpdbg'] = '--enable-phpdbg';

        $this->variants['zip'] = function (ConfigureParameters $configureParameters, Build $build) {
            if ($build->compareVersion('7.4') < 0) {
                return $configureParameters->withOption('--enable-zip');
            }

            return $configureParameters->withOption('--with-zip');
        };

        $this->variants['bcmath'] = '--enable-bcmath';
        $this->variants['fileinfo'] = '--enable-fileinfo';
        $this->variants['ctype'] = '--enable-ctype';
        $this->variants['cgi'] = '--enable-cgi';
        $this->variants['soap'] = '--enable-soap';
        $this->variants['gcov'] = '--enable-gcov';
        $this->variants['pcntl'] = '--enable-pcntl';

        $this->variants['phar'] = '--enable-phar';
        $this->variants['session'] = '--enable-session';
        $this->variants['tokenizer'] = '--enable-tokenizer';

        $this->variants['opcache'] = '--enable-opcache';

        $this->variants['imap'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $imapPrefix = Utils::findPrefix([new UserProvidedPrefix($value), new BrewPrefixFinder('imap-uw')]);

            $kerberosPrefix = Utils::findPrefix([new BrewPrefixFinder('krb5')]);

            $opensslPrefix = Utils::findPrefix([new BrewPrefixFinder('openssl'), new PkgConfigPrefixFinder('openssl'), new IncludePrefixFinder('openssl/opensslv.h')]);

            return $configureParameters->withOption('--with-imap', $imapPrefix)
                ->withOptionOrPkgConfigPath($build, '--with-kerberos', $kerberosPrefix)
                ->withOptionOrPkgConfigPath($build, '--with-imap-ssl', $opensslPrefix);
        };

        $this->variants['ldap'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new IncludePrefixFinder('ldap.h'), new BrewPrefixFinder('openldap')]);

            if ($prefix !== null) {
                $configureParameters = $configureParameters->withOption('--with-ldap', $prefix);
            }

            return $configureParameters;
        };

        $this->variants['tidy'] = '--with-tidy';
        $this->variants['kerberos'] = '--with-kerberos';
        $this->variants['xmlrpc'] = '--with-xmlrpc';

        $this->variants['fpm'] = function (ConfigureParameters $configureParameters) {
            $configureParameters = $configureParameters->withOption('--enable-fpm');

            if ($bin = Utils::findBin('systemctl') && Utils::findIncludePrefix('systemd/sd-daemon.h')) {
                $configureParameters = $configureParameters->withOption('--with-fpm-systemd');
            }

            return $configureParameters;
        };

        $this->variants['dtrace'] = fn(ConfigureParameters $configureParameters) => $configureParameters->withOption('--enable-dtrace');

        $this->variants['pcre'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            if ($build->compareVersion('7.4') >= 0) {
                return $configureParameters;
            }

            $configureParameters = $configureParameters->withOption('--with-pcre-regex');

            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new IncludePrefixFinder('pcre.h'), new BrewPrefixFinder('pcre')]);

            if ($prefix !== null) {
                $configureParameters = $configureParameters->withOption('--with-pcre-dir', $prefix);
            }

            return $configureParameters;
        };

        $this->variants['mhash'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new IncludePrefixFinder('mhash.h'), new BrewPrefixFinder('mhash')]);

            return $configureParameters->withOption('--with-mhash', $prefix);
        };

        $this->variants['mcrypt'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new IncludePrefixFinder('mcrypt.h'), new BrewPrefixFinder('mcrypt')]);

            return $configureParameters->withOption('--with-mcrypt', $prefix);
        };

        $this->variants['zlib'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new BrewPrefixFinder('zlib'), new IncludePrefixFinder('zlib.h')]);

            return $configureParameters->withOptionOrPkgConfigPath($build, '--with-zlib', $prefix);
        };

        $this->variants['curl'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new BrewPrefixFinder('curl'), new PkgConfigPrefixFinder('libcurl'), new IncludePrefixFinder('curl/curl.h')]);

            return $configureParameters->withOptionOrPkgConfigPath($build, '--with-curl', $prefix);
        };

        /*
        Users might prefer readline over libedit because only readline supports
        readline_list_history() (http://www.php.net/readline-list-history).
        On the other hand we want libedit to be the default because its license
        is compatible with PHP's which means PHP can be distributable.

        related issue https://github.com/phpbrew/phpbrew/issues/497

        The default libreadline version that comes with OS X is too old and
        seems to be missing symbols like rl_mark, rl_pending_input,
        rl_history_list, rl_on_new_line. This is not detected by ./configure

        So we should prefer macports/homebrew library than the system readline library.
        @see https://bugs.php.net/bug.php?id=48608
        */
        $this->variants['readline'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new BrewPrefixFinder('readline'), new IncludePrefixFinder('readline/readline.h')]);

            return $configureParameters->withOption('--with-readline', $prefix);
        };

        /*
         * editline is conflict with readline
         *
         * one must tap the homebrew/dupes to use this formula
         *
         *      brew tap homebrew/dupes
         */
        $this->variants['editline'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new IncludePrefixFinder('editline/readline.h'), new BrewPrefixFinder('libedit')]);

            return $configureParameters->withOption('--with-libedit', $prefix);
        };

        /*
         * It looks like gd won't be compiled without "shared"
         *
         * Suggested options is +gd=shared,{prefix}
         *
         * Issue: gd.so: undefined symbol: gdImageCreateFromWebp might happend
         *
         * Adding --with-libpath=lib or --with-libpath=lib/x86_64-linux-gnu
         * might solve the gd issue.
         *
         * The configure script in ext/gd detects libraries by something like
         * test -f $PREFIX/$LIBPATH/libxxx.a, where $PREFIX is what you passed
         * in --with-xxxx-dir and $LIBPATH can varies in different OS.
         *
         * By adding --with-libpath, you can set it up properly.
         *
         * @see https://github.com/phpbrew/phpbrew/issues/461
         */
        $this->variants['gd'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new IncludePrefixFinder('gd.h'), new BrewPrefixFinder('gd')]);

            if ($build->compareVersion('7.4') < 0) {
                $option = '--with-gd';
            } else {
                $option = '--enable-gd';
            }

            $value = 'shared';

            if ($prefix !== null) {
                $value .= ',' . $prefix;
            }

            $configureParameters = $configureParameters->withOption($option, $value);

            if ($build->compareVersion('5.5') < 0) {
                $configureParameters = $configureParameters->withOption('--enable-gd-native-ttf');
            }

            if (
                ($prefix = Utils::findPrefix([new IncludePrefixFinder('jpeglib.h'), new BrewPrefixFinder('libjpeg')])) !== null
            ) {
                if ($build->compareVersion('7.4') < 0) {
                    $option = '--with-jpeg-dir';
                } else {
                    $option = '--with-jpeg';
                }

                $configureParameters = $configureParameters->withOption($option, $prefix);
            }

            if (
                $build->compareVersion('7.4') < 0 && ($prefix = Utils::findPrefix([new IncludePrefixFinder('png.h'), new IncludePrefixFinder('libpng12/pngconf.h'), new BrewPrefixFinder('libpng')])) !== null
            ) {
                $configureParameters = $configureParameters->withOption('--with-png-dir', $prefix);
            }

            // the freetype-dir option does not take prefix as its value,
            // it takes the freetype.h directory as its value.
            //
            // from configure:
            //   for path in $i/include/freetype2/freetype/freetype.h
            if (
                ($prefix = Utils::findPrefix([new IncludePrefixFinder('freetype2/freetype.h'), new IncludePrefixFinder('freetype2/freetype/freetype.h'), new BrewPrefixFinder('freetype')])) !== null
            ) {
                if ($build->compareVersion('7.4') < 0) {
                    $option = '--with-freetype-dir';
                } else {
                    $option = '--with-freetype';
                }

                $configureParameters = $configureParameters->withOption($option, $prefix);
            }

            return $configureParameters;
        };

        /*
        --enable-intl

         To build the extension you need to install the » ICU library, version
         4.0.0 or newer is required.

         This extension is bundled with PHP as of PHP version 5.3.0.
         Alternatively, the PECL version of this extension may be used with all
         PHP versions greater than 5.2.0 (5.2.4+ recommended).

         This requires --with-icu-dir=/....

         Please note prefix must provide {prefix}/bin/icu-config for autoconf
         to find the related icu-config binary, or the configure will fail.

         Issue: https://github.com/phpbrew/phpbrew/issues/433
        */
        $this->variants['intl'] = function (ConfigureParameters $configureParameters, Build $build) {
            $configureParameters = $configureParameters->withOption('--enable-intl');

            $prefix = Utils::findPrefix([new PkgConfigPrefixFinder('icu-i18n'), new BrewPrefixFinder('icu4c')]);

            if ($build->compareVersion('7.4') < 0) {
                if ($prefix !== null) {
                    $configureParameters = $configureParameters->withOption('--with-icu-dir', $prefix);
                }
            } else {
                if ($prefix !== null) {
                    $configureParameters = $configureParameters->withPkgConfigPath($prefix . '/lib/pkgconfig');
                }
            }

            return $configureParameters;
        };

        $this->variants['sodium'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new BrewPrefixFinder('libsodium'), new PkgConfigPrefixFinder('libsodium'), new IncludePrefixFinder('sodium.h'), new LibPrefixFinder('libsodium.a')]);

            return $configureParameters->withOption('--with-sodium', $prefix);
        };

        /*
         * --with-openssl option
         *
         * --with-openssh=shared
         * --with-openssl=[dir]
         *
         * On ubuntu you need to install libssl-dev
         */
        $this->variants['openssl'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new BrewPrefixFinder('openssl'), new PkgConfigPrefixFinder('openssl'), new IncludePrefixFinder('openssl/opensslv.h')]);

            return $configureParameters->withOptionOrPkgConfigPath($build, '--with-openssl', $prefix);
        };

        /*
        quote from the manual page:

        > MySQL Native Driver is a replacement for the MySQL Client Library
        > (libmysqlclient). MySQL Native Driver is part of the official PHP
        > sources as of PHP 5.3.0.

        > The MySQL database extensions MySQL extension, mysqli and PDO MYSQL all
        > communicate with the MySQL server. In the past, this was done by the
        > extension using the services provided by the MySQL Client Library. The
        > extensions were compiled against the MySQL Client Library in order to
        > use its client-server protocol.

        > With MySQL Native Driver there is now an alternative, as the MySQL
        > database extensions can be compiled to use MySQL Native Driver instead
        > of the MySQL Client Library.

        mysqlnd should be prefered over the native client library.

        --with-mysql[=DIR]      Include MySQL support.  DIR is the MySQL base
                                directory.  If mysqlnd is passed as DIR,
                                the MySQL native driver will be used [/usr/local]

        --with-mysqli[=FILE]    Include MySQLi support.  FILE is the path
                                to mysql_config.  If mysqlnd is passed as FILE,
                                the MySQL native driver will be used [mysql_config]

        --with-pdo-mysql[=DIR]    PDO: MySQL support. DIR is the MySQL base directoy
                                If mysqlnd is passed as DIR, the MySQL native
                                native driver will be used [/usr/local]

        --with-mysql            deprecated in 7.0

        --enable-mysqlnd        Enable mysqlnd explicitly, will be done implicitly
                                when required by other extensions

        mysqlnd was added since php 5.3
        */
        $this->variants['mysql'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            if ($value === null) {
                $value = 'mysqlnd';
            }

            if ($build->compareVersion('7.0') < 0) {
                $configureParameters = $configureParameters->withOption('--with-mysql', $value);
            }

            $configureParameters = $configureParameters->withOption('--with-mysqli', $value);

            if ($build->isEnabledVariant('pdo')) {
                $configureParameters = $configureParameters->withOption('--with-pdo-mysql', $value);
            }

            $foundSock = false;
            if ($bin = Utils::findBin('mysql_config')) {
                if ($output = exec_line("$bin --socket")) {
                    $foundSock = true;
                    $configureParameters = $configureParameters->withOption('--with-mysql-sock', $output);
                }
            }

            if (!$foundSock) {
                $possiblePaths = [
                    /* macports mysql ... */
                    '/opt/local/var/run/mysql57/mysqld.sock',
                    '/opt/local/var/run/mysql56/mysqld.sock',
                    '/opt/local/var/run/mysql55/mysqld.sock',
                    '/opt/local/var/run/mysql54/mysqld.sock',
                    '/tmp/mysql.sock',
                    /* homebrew mysql sock */
                    '/var/run/mysqld/mysqld.sock',
                    /* ubuntu */
                    '/var/mysql/mysql.sock',
                ];

                foreach ($possiblePaths as $possiblePath) {
                    if (file_exists($possiblePath)) {
                        $configureParameters = $configureParameters->withOption('--with-mysql-sock', $possiblePath);
                        break;
                    }
                }
            }

            return $configureParameters;
        };

        $this->variants['sqlite'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $configureParameters = $configureParameters->withOption('--with-sqlite3', $value);

            if ($build->isEnabledVariant('pdo')) {
                $configureParameters = $configureParameters->withOption('--with-pdo-sqlite', $value);
            }

            return $configureParameters;
        };

        /**
         * The --with-pgsql=[DIR] and --with-pdo-pgsql=[DIR] requires [DIR]/bin/pg_config to be found.
         */
        $this->variants['pgsql'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new ExecutablePrefixFinder('pg_config'), new BrewPrefixFinder('libpq')]);

            $configureParameters = $configureParameters->withOption('--with-pgsql', $prefix);

            if ($build->isEnabledVariant('pdo')) {
                $configureParameters = $configureParameters->withOption('--with-pdo-pgsql', $prefix);
            }

            return $configureParameters;
        };

        $this->variants['xml'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $configureParameters = $configureParameters->withOption('--enable-dom');

            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new BrewPrefixFinder('libxml2'), new PkgConfigPrefixFinder('libxml'), new IncludePrefixFinder('libxml2/libxml/globals.h'), new LibPrefixFinder('libxml2.a')]);

            if ($build->compareVersion('7.4') < 0) {
                $configureParameters = $configureParameters->withOption('--enable-libxml');

                if ($prefix !== null) {
                    $configureParameters = $configureParameters->withOption('--with-libxml-dir', $prefix);
                }
            } else {
                $configureParameters = $configureParameters->withOption('--with-libxml');

                if ($prefix !== null) {
                    $configureParameters = $configureParameters->withPkgConfigPath($prefix . '/lib/pkgconfig');
                }
            }

            return $configureParameters
                ->withOption('--enable-simplexml')
                ->withOption('--enable-xml')
                ->withOption('--enable-xmlreader')
                ->withOption('--enable-xmlwriter')
                ->withOption('--with-xsl');
        };

        $this->variants['apxs2'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            if ($value) {
                return $configureParameters->withOption('--with-apxs2', $value);
            }

            if ($bin = Utils::findBinByPrefix('apxs2')) {
                return $configureParameters->withOption('--with-apxs2', $bin);
            }

            if ($bin = Utils::findBinByPrefix('apxs')) {
                return $configureParameters->withOption('--with-apxs2', $bin);
            }

            /* Special paths for homebrew */
            $possiblePaths = [
                // macports apxs path
                '/usr/local/opt/httpd24/bin/apxs',
                '/usr/local/opt/httpd23/bin/apxs',
                '/usr/local/opt/httpd22/bin/apxs',
                '/usr/local/opt/httpd21/bin/apxs',
                '/usr/local/sbin/apxs',
                // homebrew apxs prefix
                '/usr/local/bin/apxs',
                '/usr/sbin/apxs',
                // it's possible to find apxs under this path (OS X)
                '/usr/bin/apxs',
            ];

            $path = first_existing_executable($possiblePaths);

            return $configureParameters->withOption('--with-apxs2', $path);
        };

        $this->variants['gettext'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new IncludePrefixFinder('libintl.h'), new BrewPrefixFinder('gettext')]);

            return $configureParameters->withOption('--with-gettext', $prefix);
        };

        $this->variants['iconv'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([
                // PHP can't be compiled with --with-iconv=/usr because it uses giconv
                // https://bugs.php.net/bug.php?id=48451
                new UserProvidedPrefix($value),
                new BrewPrefixFinder('libiconv'),
            ]);

            return $configureParameters->withOption('--with-iconv', $prefix);
        };

        $this->variants['bz2'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new BrewPrefixFinder('bzip2'), new IncludePrefixFinder('bzlib.h')]);

            return $configureParameters->withOption('--with-bz2', $prefix);
        };

        $this->variants['ipc'] = fn(ConfigureParameters $configureParameters, Build $build) => $configureParameters
            ->withOption('--enable-shmop')
            ->withOption('--enable-sysvsem')
            ->withOption('--enable-sysvshm')
            ->withOption('--enable-sysvmsg');

        $this->variants['gmp'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            $prefix = Utils::findPrefix([new UserProvidedPrefix($value), new IncludePrefixFinder('gmp.h')]);

            return $configureParameters->withOption('--with-gmp', $prefix);
        };

        $this->variants['pear'] = function (ConfigureParameters $configureParameters, Build $build, $value) {
            if ($value === null) {
                $value = $build->getInstallPrefix() . '/lib/php/pear';
            }

            return $configureParameters->withOption('--with-pear', $value);
        };

        // merge virtual variants with config file
        $customVirtualVariants = Config::getConfigParam('variants');
        $customVirtualVariantsToAdd = [];

        if (!empty($customVirtualVariants)) {
            foreach ($customVirtualVariants as $key => $extension) {
                // The extension might be null
                if (!empty($extension)) {
                    $customVirtualVariantsToAdd[$key] = array_keys($extension);
                }
            }
        }

        $this->virtualVariants = array_merge($customVirtualVariantsToAdd, $this->virtualVariants);

        // create +everything variant
        $this->virtualVariants['everything'] = array_diff(
            array_keys($this->variants),
            ['apxs2', 'all'] // <- except these ones
        );
    }

    private function getConflict(Build $build, $feature)
    {
        if (isset($this->conflicts[ $feature ])) {
            $conflicts = [];

            foreach ($this->conflicts[ $feature ] as $f) {
                if ($build->isEnabledVariant($f)) {
                    $conflicts[] = $f;
                }
            }

            return $conflicts;
        }

        return false;
    }

    private function checkConflicts(Build $build)
    {
        if ($build->isEnabledVariant('apxs2') && version_compare($build->getVersion(), '5.4.0') < 0) {
            if ($conflicts = $this->getConflict($build, 'apxs2')) {
                $msgs = [];
                $msgs[] = 'PHP Version lower than 5.4.0 can only build one SAPI at the same time.';
                $msgs[] = '+apxs2 is in conflict with ' . implode(',', $conflicts);

                foreach ($conflicts as $conflict) {
                    $msgs[] = "Disabling {$conflict}";
                    $build->disableVariant($conflict);
                }

                echo implode(PHP_EOL, $msgs) . PHP_EOL;
            }
        }

        return true;
    }

    public function getVariantNames()
    {
        return array_keys($this->variants);
    }

    /**
     * Build `./configure' parameters from an enabled variant.
     *
     * @param string      $variant Variant name
     * @param string|null $value   User-provided value for the variant
     *
     * @return ConfigureParameters
     *
     * @throws Exception
     */
    private function buildEnabledVariant(Build $build, $variant, $value, ConfigureParameters $configureParameters)
    {
        if (!isset($this->variants[$variant])) {
            throw new Exception(sprintf('Variant "%s" is not defined', $variant));
        }

        // Skip if we've built it
        if (in_array($variant, $this->builtList)) {
            return $configureParameters;
        }

        // Skip if we've disabled it
        if (isset($this->disables[$variant])) {
            return $configureParameters;
        }

        $this->builtList[] = $variant;

        return $this->buildVariantFromDefinition($build, $this->variants[$variant], $value, $configureParameters);
    }

    /**
     * Build `./configure' parameters from a disabled variant.
     *
     * @param string $variant Variant name
     *
     * @return ConfigureParameters
     *
     * @throws Exception
     */
    private function buildDisabledVariant(Build $build, $variant, ConfigureParameters $configureParameters)
    {
        if (!isset($this->variants[$variant])) {
            throw new Exception(sprintf('Variant "%s" is not defined', $variant));
        }

        // Skip if we've built it
        if (in_array('-' . $variant, $this->builtList)) {
            return $configureParameters;
        }

        $this->builtList[] = '-' . $variant;

        $disabledParameters = $this->buildVariantFromDefinition(
            $build,
            $this->variants[$variant],
            null,
            new ConfigureParameters()
        );

        foreach ($disabledParameters->getOptions() as $option => $_) {
            // convert --enable-xxx to --disable-xxx
            $option = preg_replace('/^--enable-/', '--disable-', $option);

            // convert --with-xxx to --without-xxx
            $option = preg_replace('/^--with-/', '--without-', $option);

            $configureParameters = $configureParameters->withOption($option);
        }

        return $configureParameters;
    }

    /**
     * Build `./configure' parameters from a variant definition.
     *
     * @param string|array<string>|callable $definition Variant definition
     * @param string|null                   $value      User-provided value for the variant
     *
     * @return ConfigureParameters
     *
     * @throws Exception
     */
    private function buildVariantFromDefinition(Build $build, string|array|callable $definition, $value, ConfigureParameters $configureParameters)
    {
        if (is_string($definition)) {
            $configureParameters = $configureParameters->withOption($definition);
        } elseif (is_array($definition)) {
            foreach ($definition as $option => $value) {
                $configureParameters = $configureParameters->withOption($option, $value);
            }
        } elseif (is_callable($definition)) {
            $configureParameters = call_user_func_array($definition, [$configureParameters, $build, $value]);
        } else {
            throw new OopsException();
        }

        return $configureParameters;
    }

    /**
     * Build variants to configure options from php build object.
     *
     * @param Build $build The build object, contains version information
     *
     * @return ConfigureParameters
     *
     * @throws Exception
     */
    public function build(Build $build, ConfigureParameters $configureParameters = null)
    {
        $customVirtualVariants = Config::getConfigParam('variants');
        foreach (array_keys($build->getEnabledVariants()) as $variantName) {
            if (isset($customVirtualVariants[$variantName])) {
                foreach ($customVirtualVariants[$variantName] as $lib => $params) {
                    if (is_array($params)) {
                        $this->variants[$lib] = $params;
                    }
                }
            }
        }

        if ($configureParameters === null) {
            $configureParameters = new ConfigureParameters();
        }

        // reset builtList
        $this->builtList = [];

        // reset built options
        if (!$build->isEnabledVariant('all') && !$build->isEnabledVariant('neutral')) {
            // build common options
            $configureParameters = $configureParameters
                ->withOption('--disable-all')
                ->withOption('--enable-phar')
                ->withOption('--enable-session')
                ->withOption('--enable-short-tags')
                ->withOption('--enable-tokenizer');

            if ($build->compareVersion('7.4') < 0) {
                $configureParameters = $configureParameters->withOption('--with-pcre-regex');
            }

            if ($value = Utils::findIncludePrefix('zlib.h')) {
                $configureParameters = $configureParameters->withOption('--with-zlib', $value);
            }
        }

        if ($value = Utils::findLibPrefix('x86_64-linux-gnu')) {
            $configureParameters = $configureParameters->withOption('--with-libdir', 'lib/x86_64-linux-gnu');
        } elseif ($value = Utils::findLibPrefix('i386-linux-gnu')) {
            $configureParameters = $configureParameters->withOption('--with-libdir', 'lib/i386-linux-gnu');
        }

        if ($build->compareVersion('5.6') >= 0) {
            $build->enableVariant('opcache');
        }

        // enable/expand virtual variants
        foreach ($this->virtualVariants as $name => $variantNames) {
            if ($build->isEnabledVariant($name)) {
                foreach ($variantNames as $variantName) {
                    // enable the sub-variant only if it's not already enabled
                    // in order to not override a non-default value with the default
                    if (!$build->isEnabledVariant($variantName)) {
                        $build->enableVariant($variantName);
                    }
                }

                // it's a virtual variant, can not be built by buildVariant
                // method.
                $build->removeVariant($name);
            }
        }

        // Remove these enabled variant for disabled variants.
        $build->resolveVariants();

        // before we build these options from variants,
        // we need to check the enabled and disabled variants
        $this->checkConflicts($build);

        foreach ($build->getEnabledVariants() as $variant => $value) {
            $configureParameters = $this->buildEnabledVariant($build, $variant, $value, $configureParameters);
        }

        foreach ($build->getDisabledVariants() as $variant => $_) {
            $configureParameters = $this->buildDisabledVariant($build, $variant, $configureParameters);
        }

        foreach ($build->getExtraOptions() as $option) {
            $configureParameters = $configureParameters->withOption($option);
        }

        return $configureParameters;
    }
}
