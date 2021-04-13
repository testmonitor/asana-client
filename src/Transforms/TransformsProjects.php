<?php

namespace TestMonitor\Asana\Transforms;

use stdClass;
use TestMonitor\Asana\Validator;
use TestMonitor\Asana\Resources\Project;

trait TransformsProjects
{
    /**
     * @param \stdClass $project
     *
     * @return \TestMonitor\Asana\Resources\Project
     */
    protected function fromAsanaProject(stdClass $project): Project
    {
        Validator::hasProperties($project, ['gid', 'name']);

        return new Project([
            'gid' => $project->gid,
            'name' => $project->name,
        ]);
    }
}
