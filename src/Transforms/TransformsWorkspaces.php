<?php

namespace TestMonitor\Asana\Transforms;

use stdClass;
use TestMonitor\Asana\Resources\Workspace;

trait TransformsWorkspaces
{
    /**
     * @param \stdClass $workspace
     * @return \TestMonitor\Asana\Resources\Workspace
     */
    protected function fromAsanaWorkspace(stdClass $workspace): Workspace
    {
        return new Workspace([
            'gid' => $workspace->gid,
            'name' => $workspace->name,
        ]);
    }
}
