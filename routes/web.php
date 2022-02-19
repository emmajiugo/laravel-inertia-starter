<?php

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\Auth\LoginController;

Route::controller(LoginController::class)->group(function () {
    Route::get('login', 'create')->name('login');
    Route::post('login', 'store')->name('login');
    Route::post('/logout', 'destroy')->middleware('auth');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return Inertia::render('Home');
    });

    Route::get('/users', function () {
        return Inertia::render('Users/Index', [
            'users' => User::query()
                ->when(Request::input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%");
                })
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($user) => [
                    'id' => $user->id,
                    'name' => $user->name
                ]),

            'filters' => Request::only(['search']),
            'can' => [
                'createUser' => Auth::user()->can('create', User::class),
            ]
        ]);
    });

    Route::get('/users/create', function () {
        return Inertia::render('Users/Create');
    })->can('create', 'App\Models\User');

    Route::post('/users', function () {
        // validate the request
        $attributes = Request::validate([
            'name' => 'required',
            'email' => ['required', 'email'],
            'password' => 'required',
        ]);

        // create a user
        User::create($attributes);

        // redirect
        return redirect('users');
    });

    Route::get('/settings', function () {
        return Inertia::render('Settings');
    });

});
