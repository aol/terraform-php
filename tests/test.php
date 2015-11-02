<?php

require_once '../vendor/autoload.php';

use Terraform\Blocks\Resource;

$terraform = new \Terraform\Terraform();
$terraform->hi='j';
$nat = new Resource('aws_nat', 'my_nat');
$nat->name1 ='name';
echo $nat->toJson();
$terraform->nat =$nat;
echo $terraform->toJson();
 $terraform->dump();
exit();


$policy = new Resource('aws_iam_role_policy', 'foo_policy');
$policy->name = "foo_policy";
$policy->role = '${aws_iam_role.foo_role.id}';
$policy->prop = 'prop';
$policy->toJson();
//var_dump($policy);
$terraform->policy = $policy;

/*$t = new Resource('aws_iam_user', 'foo_user');
$t->name = "foo_policy";
$t->role = '${aws_iam_role.foo_role.id}';
$t->prop = 'prop';
$t->toJson();
var_dump($t);*/

$terraform->dump();
