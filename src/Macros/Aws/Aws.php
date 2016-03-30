<?php

namespace Terraform\Macros\Aws;

use Terraform\Blocks\Resource;
use Terraform\Terraform;

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
        if (!count($rules)) {
            $ingress = $defaults;
        }
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
            } else {
                $ingress[] = $b;
            }
        }
        $sg = new Resource('aws_security_group', $name);
        $sg->ingress = $ingress;
        $sg->egress = $defaults;
        $sg->vpc_id = $vpcId;
        $sg->name = $name;
        $sg->description = "$name security group";
        $sg->tags = ['Name' => $name];

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

    public static function autoscalingPolicy($name, array $policy)
    {
        $defaults = [
            'name' => $name,
            'adjustment_type' => "ChangeInCapacity",
            'cooldown' => 300,
            'scaling_adjustment' => 2,
        ];
        $policy += $defaults;

        $autoscalingPolicy = new Resource('aws_autoscaling_policy', $name);
        foreach ($policy as $key => $value) {
            $autoscalingPolicy->$key = $value;
        }

        return $autoscalingPolicy;
    }

    public static function autoscalingNotification($name, $groupNames, $topicArn, array $options = [])
    {
        $defaults = [
            'notifications' => [
                "autoscaling:EC2_INSTANCE_LAUNCH",
                "autoscaling:EC2_INSTANCE_LAUNCH_ERROR",
                "autoscaling:EC2_INSTANCE_TERMINATE",
                "autoscaling:EC2_INSTANCE_TERMINATE_ERROR",
            ],
        ];
        $options += $defaults;

        $autoscalingNotification = new Resource('aws_autoscaling_notification', $name);
        foreach ($options as $key => $value) {
            $autoscalingNotification->$key = $value;
        }
        $autoscalingNotification->group_names = (array)$groupNames;
        $autoscalingNotification->topic_arn = $topicArn;

        return $autoscalingNotification;
    }

    public static function cloudwatchMetricAlarm($name, array $policy)
    {
        $defaults = [
            'alarm_name' => $name,
            'evaluation_periods' => 1,
            'metric_name' => "CPUUtilization",
            'comparison_operator' => "GreaterThanThreshold",
            'threshold' => 60,
            'namespace' => "AWS/EC2",
            'period' => "60",
            'statistic' => "Average",
        ];
        $policy += $defaults;

        $cloudwatchMetricAlarm = new Resource('aws_cloudwatch_metric_alarm', $name);
        foreach ($policy as $key => $value) {
            $cloudwatchMetricAlarm->$key = $value;
        }

        return $cloudwatchMetricAlarm;
    }

    public static function elb($name, array $options)
    {
        $defaults = [
            'name' => $name,
            'listener' => [
                'instance_port' => 80,
                'instance_protocol' => "http",
                'lb_port' => 80,
                'lb_protocol' => "http",
            ],
            'health_check' => [
                'healthy_threshold' => 2,
                'unhealthy_threshold' => 2,
                'timeout' => 5,
                'target' => "HTTP:80/",
                'interval' => 30,
            ],
            'cross_zone_load_balancing' => true,
            'tags' => ['Name' => $name],
        ];
        $options += $defaults;

        $elb = new Resource('aws_elb', $name);
        foreach ($options as $key => $value) {
            $elb->$key = $value;
        }

        return $elb;
    }

    public static function s3Bucket($name, array $options = [])
    {
        $defaults = [
            'bucket' => $name,
            'acl' => 'private',
            'tags' => ['Name' => $name],
        ];
        $options += $defaults;

        $s3Bucket = new Resource('aws_s3_bucket', $name);
        foreach ($options as $key => $value) {
            $s3Bucket->$key = $value;
        }
        return $s3Bucket;
    }
}
