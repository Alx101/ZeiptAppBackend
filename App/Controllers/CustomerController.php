<?php

namespace App\Controllers;

use App\Models\Customer;
use App\Models\Card;
use App\Models\Session;

class CustomerController
{
    public $sessionTime;

    public function __construct()
    {
    }

    public function createCustomer($name = "", $pass = "")
    {
        $exists = Customer::where('name', $name)->first();
        if ($exists) {
            return null;
        }

        if (strlen($name) == 0 || strlen($pass) == 0) {
            return null;
        }

        $cid = md5(time().$name);
        $v = password_hash($pass, PASSWORD_BCRYPT);
        $customer = Customer::create([
            'name' => $name,
            'password' => password_hash($pass, PASSWORD_BCRYPT),
            'cid' => $cid
        ]);

        return $customer;
    }

    public function login($name, $pass)
    {
        $exists = Customer::where('name', $name)->first();
        if($exists && password_verify($pass, $exists->password)) {
            $old_sessions = Session::where('customer_id', $exists->id)->where('expired', '0')->get();
            foreach($old_sessions as $old) {
                $old->expired = true;
                $old->save();
            }

            $hash = password_hash(time().$name, PASSWORD_BCRYPT);
            Session::create([
                'customer_id' => $exists->id,
                'expired' => '0',
                'token' => $hash
            ]);

            return ['customer' => $exists, 'session_token' => $hash];
        }
        return null;
    }

    public function logout($session_id, $customer_id) {
        $exists = Session::where('customer_id', $customer_id)->where('token', $session_id)->where('expired', '0')->first();
        if ($exists) {
            $exists->expired = '1';
            $exists->save();
        }
        return true;
    }

    public function checkSession($session_id, $cid)
    {
        $customer = Customer::where('cid', $cid)->first();
        if($customer) {
            $session = Session::where('token', $session_id)->where('expired', '0')->where('customer_id', $customer->id)->first();
            if($session) {
                return $customer;
            }
        }
        return false;
    }

    public function registerCard($customer_id)
    {
        //Create a card entry into the database
        $customer = Customer::where('id', $customer_id)->first();
        if($customer) {
            Card::create([
                'customer_id' => $customer_id,
                'lastfour' => null
            ]);
            return true;
        }
        return false;
    }

    public function createCard($customer_id, $number, $expire, $type)
    {
        $card = Card::create([
            'lastfour' => $number,
            'card_expires' => $expire,
            'card_type' => $type,
            'customer_id' => $customer_id
        ]);

        if($card) {
            return $card;
        } else {
            return false;
        }
    }

    public function saveCard($customer_id, $number, $expire, $type)
    {
        $card = Card::where('customer_id', $customer_id)->whereNull('lastfour')->first();
        if($card) {
            $card->lastfour = $number;
            $card->card_expires = $expire;
            $card->card_type = $type;
            $card->save();
            return $card;
        }
        return false;
    }

    public function removeCard($customer_id, $card_id)
    {
        $card = Card::where('customer_id', $customer_id)->where('id', $card_id)->first();
        if($card) {
            $card->delete();
            return true;
        }
        return false;
    }
}