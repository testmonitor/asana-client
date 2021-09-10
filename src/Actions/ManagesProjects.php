<?php

namespace TestMonitor\Asana\Actions;

use Asana\Errors\NotFoundError;
use Asana\Errors\ForbiddenError;
use Asana\Errors\InvalidTokenError;
use Asana\Errors\NoAuthorizationError;
use TestMonitor\Asana\Resources\Project;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Transforms\TransformsProjects;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

trait ManagesProjects
{
    use TransformsProjects;

    /**
     * Get a list of of projects for a workspace.
     *
     * @param string $workspaceGid
     * @return \TestMonitor\Asana\Resources\Project[]
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     */
    public function projects($workspaceGid)
    {
        try {
            $projects = $this->client()->projects->findByWorkspace($workspaceGid);

            return array_map(function ($project) {
                return $this->fromAsanaProject($project);
            }, iterator_to_array($projects));
        } catch (NoAuthorizationError|InvalidTokenError|ForbiddenError $exception) {
            throw new UnauthorizedException($exception->getMessage());
        } catch (NotFoundError $exception) {
            throw new NotFoundException($exception->getMessage());
        }
    }

    /**
     * Get a single project.
     *
     * @param string $gid
     * @return \TestMonitor\Asana\Resources\Project
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     */
    public function project($gid): Project
    {
        try {
            $project = $this->client()->projects->findById($gid);

            return $this->fromAsanaProject($project);
        } catch (NoAuthorizationError|InvalidTokenError|ForbiddenError $exception) {
            throw new UnauthorizedException($exception->getMessage());
        } catch (NotFoundError $exception) {
            throw new NotFoundException($exception->getMessage());
        }
    }
}
