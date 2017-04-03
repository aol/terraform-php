<?php

namespace Terraform;

use Terraform\Blocks\Block;

class Terraform
{
    protected $terraform = [];

    public function __get($name)
    {
        return $this->terraform[$name];
    }

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
                    $blockText = self::serializeToHcl($block);
                    $s .= $blockText;
                    $s .= PHP_EOL . '}' . PHP_EOL;
                } else {
                    foreach ($block as $name => $values) {
                        $blockText = '';
                        $s .= PHP_EOL . $blockType;
                        $s .= ' "' . $blockName . '"';
                        $s .= ' "' . $name . '" {';
                        $blockText = self::serializeToHcl($values);
                        $s .= $blockText;
                        $s .= PHP_EOL . '}' . PHP_EOL;
                    }
                }
            }
        }
        return $s;
    }

    public static function serializeToHcl(array $values, $indentLevel = 1)
    {
        $indent = self::indent($indentLevel);

        $hcl = '';
        foreach ($values as $key => $value) {
            // handle cases where key can be specified multiple times (like ingress, egress, tag)
            // this will be an array of hashes
            if (isset($value[0]) && is_array($value[0])) {
                foreach ($value as $k => $v) {
                    $hcl .= PHP_EOL . $indent . "$key = {";
                    $hcl .= self::serializeToHcl($v, $indentLevel + 1);
                    $hcl .= PHP_EOL . $indent . "}";
                }
            } elseif (is_array($value)) {
                $hcl .= PHP_EOL . $indent . "$key = ";
                if (self::arrayIsAssociative($value)) {
                    $hcl .= '{';
                    $hcl .= self::serializeToHcl($value, $indentLevel + 1);
                    $hcl .= PHP_EOL . $indent . '}';
                } else {
                    $hcl .= self::jsonEncode($value, false);
                }
            } else {
                $hcl .= PHP_EOL . $indent . "$key = " . ((strlen($value) && $value[0] == '$') ? '"' . $value . '"' : self::jsonEncode($value));
            }
        }
        return $hcl;
    }

    public function dump()
    {
        var_dump($this->terraform);
    }

    public static function indent($level)
    {
        return str_repeat("\t", $level);
    }

    // http://stackoverflow.com/a/4254008
    public static function arrayIsAssociative(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}
