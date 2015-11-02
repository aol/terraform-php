<?php

namespace Terraform;


class Terraform
{
    protected $terraform = [];

    public function __set($name, $value)
    {
        echo "Setting '$name'" . PHP_EOL;
        // var_dump($value);
        $this->terraform[][$name] = $value;
    }

    public function save($filename = 'terraform.tf.json')
    {
        file_put_contents($filename, $this->toJson());
    }

    public function dump()
    {
        var_dump($this->terraform);
    }

    public function toJson()
    {
        $a=[];
        foreach($this->terraform as $key=>$value){
            echo 'key: ';print_r($key);
            echo 'value: ';print_r($value);

        }
        return json_encode($this->terraform, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}