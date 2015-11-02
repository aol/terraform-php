<?php

namespace Terraform\Blocks;


class Provider extends Base
{
    protected $resourceType, $resourceName;

    public function __construct($resourceType, $resourceName)
    {
        $this->block = 'provider';
        $this->resourceType = $resourceType;
        $this->resourceName = $resourceName;
        return $this;
    }
}