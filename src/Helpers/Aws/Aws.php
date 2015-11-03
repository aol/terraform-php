<?php

namespace Terraform\Helpers\Aws;

use Aws\Sdk;

class Aws
{
    protected $aws;

    public function __construct($region = 'us-east-1')
    {
        $this->aws = new Sdk([
            'region' => $region,
            'version' => 'latest',
        ]);
    }

    public function listAvailabilityZones()
    {
        $ec2 = $this->aws->createEc2();
        $result = $ec2->describeAvailabilityZones();
        return array_column($result->toArray()['AvailabilityZones'], 'ZoneName');
    }
}