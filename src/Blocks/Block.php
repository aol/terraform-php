<?php

namespace Terraform\Blocks;

class Block
{
    protected $terraform, $_block, $_type, $_name, $_data = [];

    public function __construct($block, $type, $name = null)
    {
        $this->_block = $block;
        $this->_type = $type;
        $this->_name = $name;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            return $this->_data[$name];
        }
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function toArray()
    {
        return [$this->_block => [$this->_type => $this->_data]];
    }

    public function getData()
    {
        return $this->_data;
    }

    public function dump()
    {
        print_r($this->terraform);
    }

    public function toJson()
    {
        return json_encode($this->terraform, JSON_PRETTY_PRINT);
    }
}
