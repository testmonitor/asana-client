<?php

namespace TestMonitor\Asana\Actions;

use Asana\Errors\NotFoundError;
use Asana\Errors\ForbiddenError;
use Asana\Errors\InvalidTokenError;
use Asana\Errors\InvalidRequestError;
use Asana\Errors\NoAuthorizationError;
use TestMonitor\Asana\Exceptions\NotFoundException;
use TestMonitor\Asana\Exceptions\UnauthorizedException;
use TestMonitor\Asana\Transforms\TransformsAttachments;

trait ManagesAttachments
{
    use TransformsAttachments;

    /**
     * Add a new attachment.
     *
     * @param string $path
     * @param string $taskGid
     *
     * @throws \TestMonitor\Asana\Exceptions\NotFoundException
     * @throws \TestMonitor\Asana\Exceptions\UnauthorizedException
     *
     * @return \TestMonitor\Asana\Resources\Attachment
     */
    public function addAttachment(string $path, string $taskGid)
    {
        try {
            $attachment = $this->client()->attachments->createOnTask(
                $taskGid,
                file_get_contents($path),
                basename($path),
                mime_content_type($path)
            );

            return $this->fromAsanaAttachment($attachment);
        } catch (NoAuthorizationError|InvalidTokenError|ForbiddenError $exception) {
            throw new UnauthorizedException($exception->getMessage());
        } catch (InvalidRequestError|NotFoundError $exception) {
            throw new NotFoundException($exception->getMessage());
        }
    }
}
