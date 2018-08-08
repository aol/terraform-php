<?php

namespace Terraform\Blocks;

class Data extends Block implements BlockInterface
{
    public function __construct($resourceType, $resourceName)
    {
        parent::__construct('data', $resourceType, $resourceName);
    }

    public function toArray()
    {
        return [$this->_block => [$this->_type => [$this->_name => $this->_data]]];
    }

    public function getTfProp($property = 'id', $encapsulate = true)
    {
        $resource = "{$this->_type}.{$this->_name}.{$property}";
        if ($encapsulate) {
            $resource = '${data.' . $resource . '}';
        }
        return $resource;
    }
}
