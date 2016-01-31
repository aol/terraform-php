<?php

namespace Terraform\Macros\Aws;

use Terraform\Terraform;
use Terraform\Blocks\Resource;

class Aws
{
    public static function securityGroup($name, $vpcId, array $rules)
    {
        $defaults = [
            'cidr_blocks' => ['0.0.0.0/0'],
            'from_port' => 0,
            'to_port' => 0,
            'protocol' => -1,
        ];

        $ingress = [];
        foreach ($rules as $rule) {
            $b = $defaults;
            foreach (['cidr_blocks', 'protocol'] as $key) {
                if (isset($rule[$key])) {
                    $b[$key] = $rule[$key];
                }
            }
            if (isset($rule['ports'])) {
                foreach ($rule['ports'] as $port) {
                    $b['from_port'] = $b['to_port'] = $port;
                    $ingress[] = $b;
                }
            }
        }
        $sg = new Resource('aws_security_group', $name);
        $sg->ingress = $ingress;
        $sg->egress = $defaults;
        $sg->vpc_id = $vpcId;
        $sg->name = $name;
        $sg->description = "$name security group";

        return $sg;
    }

    public static function iamRole($name, array $policy = [], $path = '/')
    {
        $defaults = [
            'Action' => 'sts:AssumeRole',
            'Principal' => ['Service' => 'ec2.amazonaws.com'],
            'Effect' => 'Allow',
            'Sid' => '',
        ];
        $policy += $defaults;

        $role = new Resource('aws_iam_role', $name);
        $role->name = $name;
        $role->path = $path;
        $role->assume_role_policy = Terraform::jsonEncode([
                'Version' => '2012-10-17',
                'Statement' => [$policy],
            ]
        );

        return $role;
    }

    public static function iamRolePolicy($name, $role, array $policy)
    {
        $defaults = [
            'Effect' => 'Allow',
        ];
        $policy += $defaults;

        $rolePolicy = new Resource('aws_iam_role_policy', $name);
        $rolePolicy->name = $name;
        $rolePolicy->role = $role;
        $rolePolicy->policy = Terraform::jsonEncode([
            'Version' => '2012-10-17',
            'Statement' => [$policy],
        ]);

        return $rolePolicy;
    }

    public static function iamInstanceProfile($name, array $roles)
    {
        $instanceProfile = new Resource('aws_iam_instance_profile', $name);
        $instanceProfile->name = $name;
        $instanceProfile->roles = $roles;

        return $instanceProfile;
    }
}
