<?php
namespace Terraform\Blocks;

class Resource extends Block
{
    public function __construct($resourceType, $resourceName)
    {
        parent::__construct('resource', $resourceType, $resourceName);
    }

    public function toArray()
    {
        return [$this->_block => [$this->_type => [$this->_name => $this->_data]]];
    }
}
