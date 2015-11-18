<?php

namespace Terraform\Blocks;

class Resource extends Block implements BlockInterface
{
    public function __construct($resourceType, $resourceName)
    {
        parent::__construct('resource', $resourceType, $resourceName);
    }

    public function toArray()
    {
        return [$this->_block => [$this->_type => [$this->_name => $this->_data]]];
    }

    public function getTfProp($property = 'id')
    {
        return "{$this->_type}.{$this->_name}.{$property}";
    }
}
