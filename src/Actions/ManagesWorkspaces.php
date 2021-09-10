<?php

namespace TestMonitor\Asana\Actions;

use Asana\Errors\NotFoundError;
use Asana\Errors\ForbiddenError;
use Asana\Errors\InvalidTokenError;
use Asana\Errors\NoAuthorizationError;
use TestMonitor\Asana\Resources\Workspace;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Transforms\TransformsWorkspaces;
use TestMonitor\Asana\Exceptions\UnauthorizedException;

trait ManagesWorkspaces
{
    use TransformsWorkspaces;

    /**
     * Get a list of of workspaces.
     *
     * @return \TestMonitor\Asana\Resources\Workspace[]
     *
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     */
    public function workspaces()
    {
        try {
            $workspaces = $this->client()->workspaces->findAll();

            return array_map(function ($workspace) {
                return $this->fromAsanaWorkspace($workspace);
            }, iterator_to_array($workspaces));
        } catch (NoAuthorizationError|InvalidTokenError|ForbiddenError $exception) {
            throw new UnauthorizedException($exception->getMessage());
        }
    }

    /**
     * Get a single workspace.
     *
     * @param  string  $gid
     * @return \TestMonitor\Asana\Resources\Workspace
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     */
    public function workspace($gid): Workspace
    {
        try {
            $workspace = $this->client()->workspaces->findById($gid);

            return $this->fromAsanaWorkspace($workspace);
        } catch (NoAuthorizationError|InvalidTokenError|ForbiddenError $exception) {
            throw new UnauthorizedException($exception->getMessage());
        } catch (NotFoundError $exception) {
            throw new NotFoundException($exception->getMessage());
        }
    }
}
