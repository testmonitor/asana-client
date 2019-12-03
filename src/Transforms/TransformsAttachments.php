<?php

namespace TestMonitor\Asana\Transforms;

use stdClass;
use TestMonitor\Asana\Resources\Attachment;

trait TransformsAttachments
{
    /**
     * @param \stdClass $attachment
     *
     * @return \TestMonitor\Asana\Resources\Attachment
     */
    protected function fromAsanaAttachment(stdClass $attachment): Attachment
    {
        return new Attachment([
            'gid' => $attachment->gid,
            'name' => $attachment->name,
        ]);
    }
}
