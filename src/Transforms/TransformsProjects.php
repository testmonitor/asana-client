<?php

namespace TestMonitor\Asana\Transforms;

use TestMonitor\Asana\Validator;
use TestMonitor\Asana\Resources\Project;

trait TransformsProjects
{
    /**
     * @param array $project
     * @return \TestMonitor\Asana\Resources\Project
     */
    protected function fromAsanaProject(array $project): Project
    {
        Validator::keysExists($project, ['gid', 'name']);

        return new Project($project);
    }
}
