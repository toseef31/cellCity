<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\Tech;
use Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }



  public function accountLogin(Request $request)
    {
        if($request->isMethod('post')){
           
           if(Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password, 'role' => 'user'])){
        
        if(Auth::guard('web')->check()){
          
            return redirect(RouteServiceProvider::HOME);

                }
            }
        }
  

     return view('frontend.signin');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        if($request->isMethod('post')){
            // dd($request->all());
            
                $user = new User;
                $user->name = $request->name;
                $user->email =  $request->email;
                $user->address =  $request->address;
                $user->phoneno =  $request->phoneno;
                $user->role = $request->role;
                $user->password = Hash::make($request->password);
                $user->save();
            
           
        }
        
        return view('frontend.signup');
      
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}