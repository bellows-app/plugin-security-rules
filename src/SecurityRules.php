<?php

namespace Bellows\Plugins;

use Bellows\PluginSdk\Contracts\Deployable;
use Bellows\PluginSdk\Data\SecurityRule;
use Bellows\PluginSdk\Facades\Console;
use Bellows\PluginSdk\Facades\Project;
use Bellows\PluginSdk\Plugin;
use Bellows\PluginSdk\PluginResults\CanBeDeployed;
use Bellows\PluginSdk\PluginResults\DeploymentResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SecurityRules extends Plugin implements Deployable
{
    use CanBeDeployed;

    public function defaultForDeployConfirmation()
    {
        return Str::contains(Project::domain(), ['dev.', 'staging.']);
    }

    public function deploy(): ?DeploymentResult
    {
        return DeploymentResult::create()->securityRules($this->getSecurityRules());
    }

    public function shouldDeploy(): bool
    {
        return true;
    }

    protected function getSecurityRules(): Collection
    {
        $rules = collect();

        do {
            $groupName = Console::ask(
                'Security rule group name',
                'Restricted Access'
            );

            $path = Console::ask(
                'Path (leave blank to password protect all routes within your site, any valid Nginx location path)'
            );

            $credentials = collect();

            do {
                $username = Console::ask('Username');
                $password = Console::secret('Password');

                $credentials->push([
                    'username' => $username,
                    'password' => $password,
                ]);
            } while (Console::confirm('Add another user?'));

            $rules->push(
                SecurityRule::from([
                    'name'        => $groupName,
                    'path'        => $path,
                    'credentials' => $credentials,
                ])
            );
        } while (Console::confirm('Add another security rule group?'));

        return $rules;
    }
}
