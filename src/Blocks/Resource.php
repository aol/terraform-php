<?php
namespace Terraform\Blocks;

class Resource extends Base
{
    protected $type, $name;

    public function __construct($resourceType, $resourceName)
    {
        $this->block = 'resource';
        $this->type = $resourceType;
        $this->name = $resourceName;
    }
}
