<?php

namespace App\Http\Controllers;

use DB;
use App;
use App\Models\User;
use App\Models\Orders;

class MyController extends Controller
{

    public function __construct()
    {
        $this->template = env('THEME_RESOURCES') . '.index';
    }


    static public function change_phones()
    {
        $users = User::all();

        foreach($users as $user)
        {
            $save_user = User::where('id', $user->id)->first();
            $save_user->phone = $save_user->phone;
            $save_user->save();
        }

        echo "ok";
    }

    static public function change_delivery_phones()
    {
        $orders = Orders::all();

        foreach($orders as $order)
        {
            $delivery = '';
            $order_user = Orders::where('id', $order->id)->first();
            $delivery = json_decode($order_user->delivery, true);
            if(isset($delivery["phone"]))
            {
                $delivery['phone'] = str_replace(" ","", $delivery['phone']);
                $delivery['phone'] = str_replace(")","", $delivery['phone']);
                $delivery['phone'] = str_replace("(","", $delivery['phone']);
                $delivery['phone'] = str_replace("-","", $delivery['phone']);
                $order_user->delivery = json_encode($delivery, true);
                $order_user->save();
                //echo $delivery['phone']."<br>"; 
            }
        }

        echo "ok";
    }

}
