<?php

namespace TestMonitor\Asana\Actions;

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
        $attachment = $this->post(
            "tasks/{$taskGid}/attachments",
            [
                'files' => [
                    'file' => [
                        'content' => file_get_contents($path),
                        'filename' => basename($path),
                        'contentType' => mime_content_type($path),
                    ],
                ],
            ]
        );

        return $this->fromAsanaAttachment($attachment['data']);
    }
}
