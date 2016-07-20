# terraform-php
Use PHP to generate Terraform configuration files

## Overview
Terraform is great, but HCL is a configuration language (like JSON or YAML), not a programming language.  Although it has some language primitives, it lacks a number of things such as loops and if statements.  It would be great if we could just "code," right?  This project allows you to do just that.  You write in pure PHP, and generate fully-valid Terraform configs in either HCL or JSON, your choice.

Additionally, this project provides some helper functions that talk to the respective APIs (AWS only at the moment) that allow for dynamic config generation.  For example, you can lookup a VPC's CIDR block, perform some math, and create an ENI (via Terraform) for the first available IP in each subnet.

There are also macros for some commonly-used functions that provide sensible defaults.

## Installation
This is meant to be used as a library for your own PHP-based projects.  As such, having the following in your composer.json will load this project and its dependencies.
```
  "repositories": [
    {
      "type": "git",
      "url": "https://github.com/aol/terraform-php"
    }
  ],
  "require": {
    "aol/terraform-php": "dev-develop"
  }
```

## Usage
This project uses PHP magic methods (namely `__set()` and `__get()`), and does not hardcode support for any Terraform resources.  That means that we're automatically compatible with any new resources that Terraform implements. 
Below is an example of what using this project could look like.  Note the use of macros for creating security groups, and the manual creation of the `aws_elasticache_cluster` resource.

```
$projectLongName = 'My Test Project';
$projectShortName = 'mtp';

$vpcId = 'vpc-xxxxxxxx';

// create security group for web hosts and ELB
$rules = [
    [
        'cidr_blocks' => ['0.0.0.0/0'],
        'ports' => [80, 443],
        'protocol' => 'tcp',
    ],
    [
        'cidr_blocks' => ['172.31.0.0/16'],
    ],
];
$sgWeb = AwsMacros::securityGroup("$projectShortName-web", $vpcId, $rules);
$sgWeb->description = "Allow web traffic for $projectLongName";
$terraform->sgWeb = $sgWeb;

// create security group for Redis
$rules = [
    [
        'cidr_blocks' => ['172.31.0.0/16'],
    ],
];
$sgRedis = AwsMacros::securityGroup("$projectShortName-redis", $vpcId, $rules);
$sgRedis->description = "$projectLongName Redis";
$terraform->sgRedis = $sgRedis;

foreach (['prod', 'dev', 'staging'] as $env) {
    // create redis cluster for multiple environments
    $name = "{$projectShortName}-{$env}-redis";
    $tags['Name'] = $name;

    $redis = new Resource('aws_elasticache_cluster', $name);
    $redis->cluster_id = substr($name, 0, 20);
    $redis->engine = 'redis';
    $redis->node_type = 'cache.t2.micro';
    $redis->num_cache_nodes = 1;
    $redis->parameter_group_name = 'default.redis2.8';
    $redis->port = 6379;
    $redis->subnet_group_name = 'xxxxx';
    $redis->security_group_ids = [$sgRedis->getTfProp('id')];
    $redis->tags = $tags;
    $terraform->{"redis_$name"} = $redis;
}
```
