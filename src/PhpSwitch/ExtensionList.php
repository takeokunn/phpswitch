<?php

namespace PhpSwitch;

use CLIFramework\Logger;
use GetOptionKit\OptionResult;
use PhpSwitch\Extension\Provider\Provider;

class ExtensionList
{
    public function __construct(private readonly Logger $logger, private readonly OptionResult $optionResult)
    {
    }

    /**
     * Returns available extension providers
     *
     * @return Provider[]
     */
    public function getProviders()
    {
        static $providers;
        if ($providers) {
            return $providers;
        }
        $providers = [new Extension\Provider\GithubProvider(), new Extension\Provider\BitbucketProvider(), new Extension\Provider\PeclProvider($this->logger, $this->optionResult)];

        return $providers;
    }

    /**
     * Returns provider for the given extension
     *
     * @param string $extensionName
     * @return Provider|null
     */
    public function exists($extensionName)
    {
        // determine which provider support this extension
        $providers = $this->getProviders();
        foreach ($providers as $provider) {
            if ($provider->exists($extensionName)) {
                return $provider;
            }
        }

        return null;
    }
}
