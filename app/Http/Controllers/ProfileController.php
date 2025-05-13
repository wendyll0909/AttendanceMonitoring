<?php

     namespace App\Http\Controllers;

     use Illuminate\Http\Request;
     use Illuminate\Support\Facades\Auth;

     class ProfileController extends Controller
     {
         public function edit()
         {
             return view('profile.edit', ['user' => Auth::user()]);
         }

         public function update(Request $request)
         {
             $request->validate([
                 'name' => ['required', 'string', 'max:255'],
                 'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
             ]);

             Auth::user()->update($request->only('name', 'email'));

             return redirect()->route('profile.edit')->with('status', 'Profile updated!');
         }
     }
     ?>