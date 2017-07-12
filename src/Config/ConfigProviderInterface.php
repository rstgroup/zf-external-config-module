<?php


namespace RstGroup\ZfExternalConfigModule\Config;


interface ConfigProviderInterface
{
    /**
     * This method returns the config from given provider.
     *
     * Provider itself should be configured while constructing, so no further params are required
     * to fetch configuration data.
     *
     * @return array
     */
    public function getConfig();
}
