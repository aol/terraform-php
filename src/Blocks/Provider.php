<?php

namespace Terraform\Blocks;


class Provider extends Block
{
    public function __construct($resourceType)
    {
        parent::__construct('provider', $resourceType);
    }
}