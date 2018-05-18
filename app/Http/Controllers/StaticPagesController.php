<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
class StaticPagesController extends Controller
{
    public function home()
    {
        $feed_items  = [];
        if (Auth::check()){
            $feed_items = Auth::user()->feed()->paginate(10);
            //dd($statuses->toArray());
        }
    	return view('static_pages/home',compact('feed_items'));
    }
    public function help()
    {
    	return view('static_pages/help');
    }
    public function about()
    {
    	return view('static_pages/about');
    }
}
