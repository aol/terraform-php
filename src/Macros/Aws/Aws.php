<?php

namespace Terraform\Macros\Aws;

use Terraform\Blocks\Resource;

class Aws
{
    public static function securityGroup($name, $vpcId, $cidrBlocks = ["0.0.0.0/0"], $fromPort = 0, $toPort = 0, $protocol = -1)
    {
        $sg = new Resource('aws_security_group', $name);
        $sg->egress = [
            "cidr_blocks" => ["0.0.0.0/0"],
            "from_port" => 0,
            "to_port" => 0,
            "protocol" => -1,
        ];
        $sg->ingress = [
            "cidr_blocks" => $cidrBlocks,
            "from_port" => $fromPort,
            "to_port" => $toPort,
            "protocol" => $protocol,
        ];
        $sg->vpc_id = $vpcId;
        $sg->name = $name;
        $sg->description = "$name security group";
        return $sg;
    }
}