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
        $exists = (Customer::where('name', $name)->first() != null);
        if($exists)
            return false;

        $cid = md5(time().$name);

        $customer = Customer::create([
            'name' => $name,
            'cid' => $cid
        ]);

        return true;
    }
}