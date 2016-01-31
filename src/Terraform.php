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

    public function save($format = 'json', $filename = null)
    {
        if ($filename === null) {
            $filename = "terraform.tf" . ($format == 'json' ? '.json' : '');
        }
        file_put_contents($filename, $format == 'json' ? $this->toJson() : $this->toHcl());
    }

    public function deepMerge()
    {
        $a = [];
        foreach ($this->terraform as $key => $value) {
            $a = array_merge_recursive($a, $value->toArray());
        }

        return $a;
    }

    public function toHcl()
    {
        $a = $this->deepMerge();

        return self::hclEncode($a);
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
                        $blockText .= "\n$name = " . self::serializeToHcl($values);
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
                            // handle case when multiple rules are specified in SG
                            if (in_array($key, ['ingress', 'egress']) && isset($value[0])) {
                                foreach ($value as $v) {
                                    $blockText .= "\n$key = " . self::serializeToHcl($v);
                                }
                            } else {
                                $blockText .= "\n$key = " . self::serializeToHcl($value);
                            }
                        }
                        $s .= str_replace("\n", "\n\t", $blockText);
                        $s .= PHP_EOL . '}' . PHP_EOL;
                    }
                }
            }
        }
        return $s;
    }

    public static function serializeToHcl($value)
    {
        $json = self::jsonEncode($value);
        // HCL and JSON have the same array syntax
        if (isset($value[0])) {
            $hcl = $json;
        } else {
            // replace ': ' in JSON with ' = '
            $hcl = preg_replace('/((\s+)?"(\w+)"):\s/', '$1 = ', $json);
            // remove trailing commas
            $hcl = preg_replace('/,\n/', "\n", $hcl);
        }

        return $hcl;
    }

    public function dump()
    {
        var_dump($this->terraform);
    }
}
