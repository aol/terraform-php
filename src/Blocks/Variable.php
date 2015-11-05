<?php

namespace Terraform\Blocks;

class Variable extends Block implements BlockInterface
{
    public function __construct($variableName, $variableValues)
    {
        parent::__construct('variable', $variableName);
        $this->default = $variableValues;
    }
}