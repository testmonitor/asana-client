<?php

namespace TestMonitor\Asana\Transforms;

use TestMonitor\Asana\Validator;
use TestMonitor\Asana\Resources\Attachment;

trait TransformsAttachments
{
    /**
     * @param array $attachment
     * @return \TestMonitor\Asana\Resources\Attachment
     */
    protected function fromAsanaAttachment(array $attachment): Attachment
    {
        Validator::keysExists($attachment, ['gid', 'name']);

        return new Attachment($attachment);
    }
}
