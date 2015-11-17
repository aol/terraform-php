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

    public function listAvailabilityZones($options = [])
    {
        $ec2 = $this->aws->createEc2();
        $result = $ec2->describeAvailabilityZones($options);

        return array_column($result->toArray()['AvailabilityZones'], 'ZoneName');
    }

    public function listVpcs($options = [])
    {
        $ec2 = $this->aws->createEc2();
        $result = $ec2->describeVpcs($options);

        return array_column($result->toArray()['Vpcs'], 'VpcId');
    }

    public function getSdk()
    {
        return $this->aws;
    }
}
