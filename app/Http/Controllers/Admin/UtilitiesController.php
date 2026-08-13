<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class UtilitiesController extends Controller
{
    public function clearCache()
    {
        Artisan::call('cache:clear');
        $notification = array(
            'message' => __('admin/new.cache_is_cleared'),
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
    public function clearRoute()
    {
        Artisan::call('route:clear');
        $notification = array(
            'message' => __('admin/new.route_is_cleared'),
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
    public function clearConfig()
    {
        Artisan::call('config:clear');
        $notification = array(
            'message' => __('admin/new.config_is_cleared'),
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
    public function clearView()
    {
        Artisan::call('view:clear');
        $notification = array(
            'message' => __('admin/new.view_is_cleared'),
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
    public function clearAll()
    {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        $notification = array(
            'message' => 'All is clear',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
    public function createStorageLink()
    {
        Artisan::call('storage:link');
        $notification = array(
            'message' => 'Storage link created',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function sidebarToggler()
    {
        $sidebarToggler = request()->session()->get('sidebarToggler', 'not-active');

        if ($sidebarToggler == 'not-active')
        {
            session(['sidebarToggler' => 'active']);
        } else {
            session(['sidebarToggler' => 'not-active']);
        }

        $value = session('sidebarToggler');
        return response()->json(['sidebarToggler' => $value]);
    }
}
