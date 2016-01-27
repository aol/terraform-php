<?php

namespace Terraform;

use Terraform\Blocks\Block;

class Terraform
{
    protected $terraform = [];

    public function __set($name, $value)
    {
        if (!($value instanceof Block)) {
            throw new \Exception('Value must be a type of block.');
        }
        if (isset($this->terraform[$name])) {
            fwrite(STDERR, "Warning: $name is already set." . PHP_EOL);
        }
        $this->terraform[$name] = $value;
    }

    public function save($filename = 'terraform.tf.json')
    {
        file_put_contents($filename, $this->toJson());
    }

    public function toJson()
    {
        $a = [];
        foreach ($this->terraform as $key => $value) {
            $a = array_merge_recursive($a, $value->toArray());
        }

        return json_encode($a, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function dump()
    {
        var_dump($this->terraform);
    }
}
