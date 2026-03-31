<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:newsletters,email',
        ]);

        // Store the email (you can create a Newsletter model/table later)
        // For now, we'll just return a success response
        
        return redirect()->back()->with('success', 'Thanks for subscribing! Check your email for confirmation.');
    }
}
