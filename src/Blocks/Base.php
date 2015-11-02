<?php

namespace Terraform\Blocks;

class Base
{
    protected $terraform, $block, $type, $name;

    public function __set($name, $value)
    {
        echo "Setting '$name' to '$value'\n";
        $this->terraform[$this->block][$this->type][$this->name][$name] = $value;
    }

    public function __get($name)
    {
        echo "Getting '$name'\n";
        if (array_key_exists($name, $this->terraform[$this->block])) {
            return $this->terraform[$this->block][$name];
        }
    }

    public function dump()
    {
        echo "dumping all" . PHP_EOL;
        print_r($this->terraform);
    }

    public function toJson()
    {
        return json_encode($this->terraform, JSON_PRETTY_PRINT);
    }
}
