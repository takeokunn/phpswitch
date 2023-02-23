<?php

namespace PhpSwitch\Patches;

use CLIFramework\Logger;
use PhpSwitch\Buildable;
use PhpSwitch\PatchKit\Patch;
use PhpSwitch\PatchKit\RegExpPatchRule;

class OpenSSLDSOPatch extends Patch
{
    public function desc()
    {
        return 'openssl dso linking patch';
    }

    public function match(Buildable $buildable, Logger $logger)
    {
        return $buildable->osName === 'Darwin'
            && version_compare($buildable->osRelease, '15.0.0') > 0
            && $buildable->isEnabledVariant('openssl');
    }

    public function rules()
    {
        /*
        Macports
         -lssl /opt/local/lib/libssl.dylib
         -lcrypto /opt/local/lib/libcrypto.dylib

        HomeBrew
         /usr/local/opt/openssl/lib/libssl.dylib
         /usr/local/opt/openssl/lib/libcrypto.dylib
        */
        $dylibssl = null;
        $dylibcrypto = null;

        $paths = ['/opt/local/lib/libssl.dylib', '/usr/local/opt/openssl/lib/libssl.dylib', '/usr/local/lib/libssl.dylib', '/usr/lib/libssl.dylib'];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $dylibssl = $path;
                break;
            }
        }

        $paths = ['/opt/local/lib/libcrypto.dylib', '/usr/local/opt/openssl/lib/libcrypto.dylib', '/usr/local/lib/libcrypto.dylib', '/usr/lib/libcrypto.dylib'];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                $dylibcrypto = $path;
                break;
            }
        }

        $rules = [];
        if ($dylibssl) {
            $rules[] = RegExpPatchRule::files('Makefile')
                ->allOf(['/^EXTRA_LIBS =/'])
                ->replaces('/-lssl/', $dylibssl);
        }
        if ($dylibcrypto) {
            $rules[] = RegExpPatchRule::files('Makefile')
                ->allOf(['/^EXTRA_LIBS =/'])
                ->replaces('/-lcrypto/', $dylibcrypto);
        }

        return $rules;
    }
}
