# ZF External Config Module

This module provides the logic and abstraction for loading and merging application's 
configuration from external sources.

The module itself does not have any external provider yet, providers can be installed
via Composer or written it your application's code.

## Installation

You have to add the repository to your `composer.json` file:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@gitlab.trans-dev.loc:devops/zf-external-config-module.git"
        }
    ]
}
```

...and require module with Composer:

```bash
composer require rstgroup/zf-external-config-module
```

The last step is adding module to ZF system configuration (`config/application.config.php`):
```php
return [
    'modules' => [
        (...),
        'RstGroup\ZfExternalConfigModule',
    ],
    (...)
]
```

## Adding providers

To load configuration from external source, the providers should be registered in your app's configuration:

```php
return [
    'rst_group' => [
        'external_config' => [
            'providers' => [
                'YourProviderService' => true,
            ],
            'service_manager' => [
                'factories' => [
                    'YourProviderService' => YourProvidersServiceFactory::class
                ]
            ]
        ],
    ]
];
```

The module skims through enabled providers defined in: 
```php
rst_group->external_config->providers
````
and creates them using Inner Service Manager.

### Enabling and disabling providers

Disabled providers, even though they are defined, don't participate in creation of
final configuration. 

Here's an example how to mark defined provider enabled or disabled:
 
```php
return [
    'rst_group' => [
        'external_config' => [
            'providers' => [
                
                'EnabledProvider#1' => true,
                'EnabledProvider#2' => 'true',
                'EnabledProvider#3' => 'on',
                'EnabledProvider#4' => 1,
                
                'DisabledProvider#1' => false,
                'DisabledProvider#2' => 'false',
                'DisabledProvider#3' => 'off',
                'DisabledProvider#4' => 0,
                
            ],
        ],
    ],
];
```

### Inner Service Manager

In the example above you can see `service_manager` definition. 

The module attaches itself to the configuration merge process, thus the application's default `ServiceManager`
is not yet available. In order to keep the service manager's functionality, `zf-external-config-module`
creates its own manager!

Your service can be created by factory, can be declared invokable etc. - the same way you
normally declare services. 

>   But remember, this service ends its life just after external configuration is loaded
>   and merged!

### Passing configuration to your providers

When defining your own providers you might require to pass some credentials or other
parameters into the provider instance. To do so - the best way is to use factory
functionality, because factories have the (inner) service manager injected! 

Inner Service Manager has the application configuration and zf-external-config-module's 
configuration registered as services:

```php
use Psr\Container\ContainerInterface;
use \RstGroup\ZfExternalConfigModule\Config\ExternalConfigListener;

final class ExampleServiceFactory {
    public function __invoke(ContainerInterface $container) {
        /* getting app's configuration */
        $appConfig = $container->get(
            ExternalConfigListener::SERVICE_APPLICATION_CONFIG
        );
        
        /* getting zf-external-config-module configuration */
        $moduleConfig = $container->get(
            ExternalConfigListener::SERVICE_EXTERNALS_CONFIG
        );
        
        (...)
    }
}
```

>   Note that the `rst_group -> external_config` key is removed from application's configuration,
>   but you can still get it using module's config.

## Writing your own provider

External config providers have to implement `\RstGroup\ZfExternalConfigModule\Config\ConfigProviderInterface`
and be registered in module and inner service manager. Those are the only requirements!  

You can store the code in your own application's codebase, but we recommend to
share it with the community and publish it on GitHub & Composer :) We'll gladly add your
provider to this package's `suggest`ed providers! 

## Suggested providers

Consul KV Storage: [rstgroup/zf-external-config-consul-provider](https://gitlab.trans-dev.loc/devops/zf-external-config-consul-provider)
