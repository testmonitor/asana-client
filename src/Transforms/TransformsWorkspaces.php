<?php

namespace TestMonitor\Asana\Transforms;

use TestMonitor\Asana\Validator;
use TestMonitor\Asana\Resources\Workspace;

trait TransformsWorkspaces
{
    /**
     * @param array $workspace
     * @return \TestMonitor\Asana\Resources\Workspace
     */
    protected function fromAsanaWorkspace(array $workspace): Workspace
    {
        Validator::keysExists($workspace, ['gid', 'name']);

        return new Workspace($workspace);
    }
}
