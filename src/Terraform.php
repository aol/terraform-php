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

    public function deepMerge()
    {
        $a = [];
        foreach ($this->terraform as $key => $value) {
            $a = array_merge_recursive($a, $value->toArray());
        }

        return $a;
    }

    public function toJson()
    {
        $a = $this->deepMerge();

        return self::jsonEncode($a);
    }

    public static function jsonEncode($input)
    {
        return json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function hclEncode($input)
    {
        foreach ($input as $blockType => $blocks) {
            foreach ($blocks as $blockName => $block) {
                foreach ($block as $name => $values) {
                    echo PHP_EOL . $blockType;
                    echo ' "' . $blockName . '"';
                    echo ' "' . $name . '" {';
                    foreach ($values as $key => $value) {
                        echo "\n\t$key = " . self::jsonEncode($value);
                    }
                }
                echo PHP_EOL . '}' . PHP_EOL;
            }
        }
    }

    public function dump()
    {
        var_dump($this->terraform);
    }
}
