<?php

namespace TestMonitor\Asana\Actions;

use TestMonitor\Asana\Resources\Workspace;
use TestMonitor\Asana\Transforms\TransformsWorkspaces;

trait ManagesWorkspaces
{
    use TransformsWorkspaces;

    /**
     * Get a list of of workspaces.
     *
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     *
     * @return \TestMonitor\Asana\Resources\Workspace[]
     */
    public function workspaces()
    {
        $workspaces = $this->get('workspaces');

        return array_map(function ($workspace) {
            return $this->fromAsanaWorkspace($workspace);
        }, $workspaces['data']);
    }

    /**
     * Get a single workspace.
     *
     * @param string $gid
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     *
     * @return \TestMonitor\Asana\Resources\Workspace
     */
    public function workspace($gid): Workspace
    {
        $workspace = $this->get("workspaces/{$gid}");

        return $this->fromAsanaWorkspace($workspace);
    }
}
