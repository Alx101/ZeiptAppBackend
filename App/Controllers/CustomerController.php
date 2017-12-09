<?php

namespace App\Controllers;

use App\Models\Customer;

class CustomerController
{
    public function __construct()
    {
    }

    public function createCustomer($name = "")
    {
        $exists = Customer::where('name', $name)->first();
        if($exists)
            return $exists;

        $cid = md5(time().$name);

        $customer = Customer::create([
            'name' => $name,
            'cid' => $cid
        ]);

        return $customer;
    }
}