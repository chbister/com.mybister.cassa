<?php

namespace App\Http\Controllers;

use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Setup');
    }

    public function store(Request $request): RedirectResponse
    {
        if (User::exists()) {
            return redirect('/');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        (new DemoDataSeeder)->run();

        return redirect('/login');
    }
}
