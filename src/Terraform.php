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

    public static function jsonEncode($input, $pretty = true)
    {
        $flag = $pretty ? JSON_PRETTY_PRINT : 0;
        return json_encode($input, $flag | JSON_UNESCAPED_SLASHES);
    }

    public static function hclEncode($input)
    {
        $s = '';
        foreach ($input as $blockType => $blocks) {
            foreach ($blocks as $blockName => $block) {
                // these blocks are treated differently
                if (in_array($blockType, ['variable', 'provider'])) {
                    $blockText = '';
                    $s .= PHP_EOL . $blockType;
                    $s .= ' "' . $blockName . '"';
                    $s .= ' {';
                    foreach ($block as $name => $values) {
                        $blockText .= "\n$name = " . json_encode($values, JSON_PRETTY_PRINT);
                    }
                    $s .= str_replace("\n", "\n\t", $blockText);
                    $s .= PHP_EOL . '}' . PHP_EOL;
                } else {
                    foreach ($block as $name => $values) {
                        $blockText = '';
                        $s .= PHP_EOL . $blockType;
                        $s .= ' "' . $blockName . '"';
                        $s .= ' "' . $name . '" {';
                        foreach ($values as $key => $value) {
                            $blockText .= "\n$key = " . json_encode($value, JSON_PRETTY_PRINT);
                        }
                        $s .= str_replace("\n", "\n\t", $blockText);
                        $s .= PHP_EOL . '}' . PHP_EOL;
                    }
                }
            }
        }
    }

    public function dump()
    {
        var_dump($this->terraform);
    }
}
