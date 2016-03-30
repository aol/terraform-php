<?php

namespace Terraform\Helpers\Aws;

use Aws\Sdk;

class Aws
{
    protected $aws;

    public function __construct($region = null)
    {
        $region = $region ?: getenv('AWS_DEFAULT_REGION');

        $this->aws = new Sdk([
            'region' => $region,
            'version' => 'latest',
        ]);
    }

    public function listAvailabilityZones($options = [], $fullResponse = false)
    {
        $ec2 = $this->aws->createEc2();
        $result = $ec2->describeAvailabilityZones($options);

        return $fullResponse ? $result->toArray() : array_column($result->toArray()['AvailabilityZones'], 'ZoneName');
    }

    public function listVpcs($options = [], $fullResponse = false)
    {
        $ec2 = $this->aws->createEc2();
        $result = $ec2->describeVpcs($options);

        return $fullResponse ? $result->toArray() : array_column($result->toArray()['Vpcs'], 'VpcId');
    }

    public function listSubnets($options = [], $fullResponse = false)
    {
        $ec2 = $this->aws->createEc2();
        $result = $ec2->describeSubnets($options);

        return $fullResponse ? $result->toArray() : array_column($result->toArray()['Subnets'], 'SubnetId');
    }

    public function getSdk()
    {
        return $this->aws;
    }
}
