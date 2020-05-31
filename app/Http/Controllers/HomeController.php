<?php

namespace App\Http\Controllers;

use App\User;
use Auth;

class HomeController extends Controller
{
    public function __construct()
    {

        $this->middleware(['auth', 'verified']);
    }

    public function index()
    {

        return view('home');

    }

    public function dashboard()
    {

        $store_id = Auth::user()->store_id;

        if ($store_id == null) {

            $msg = "You do not have any Store. Add one to stat with.";

            return view('store.create', ['msg' => $msg]);
        } else {
            return view('dashboard');
        }
    }
}
