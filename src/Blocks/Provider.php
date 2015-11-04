<?php

namespace Terraform\Blocks;


class Provider extends Block implements BlockInterface
{
    public function __construct($resourceType)
    {
        parent::__construct('provider', $resourceType);
    }
}