<?php

namespace TestMonitor\Asana\Transforms;

use stdClass;
use TestMonitor\Asana\Validator;
use TestMonitor\Asana\Resources\Workspace;

trait TransformsWorkspaces
{
    /**
     * @param  \stdClass  $workspace
     * @return \TestMonitor\Asana\Resources\Workspace
     */
    protected function fromAsanaWorkspace(stdClass $workspace): Workspace
    {
        Validator::hasProperties($workspace, ['gid', 'name']);

        return new Workspace([
            'gid' => $workspace->gid,
            'name' => $workspace->name,
        ]);
    }
}
