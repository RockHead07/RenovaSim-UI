@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

<div class="space-y-6">

    <!-- PROFILE INFO -->
    <div class="bg-[#1a1a1a] border border-gray-800 rounded-xl p-6">
        <h3 class="text-white font-semibold mb-4">
            Profile Information
        </h3>

        <div class="max-w-xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <!-- UPDATE PASSWORD -->
    <div class="bg-[#1a1a1a] border border-gray-800 rounded-xl p-6">
        <h3 class="text-white font-semibold mb-4">
            Update Password
        </h3>

        <div class="max-w-xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <!-- DELETE ACCOUNT -->
    <div class="bg-[#1a1a1a] border border-gray-800 rounded-xl p-6">
        <h3 class="text-red-400 font-semibold mb-4">
            Delete Account
        </h3>

        <div class="max-w-xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>

</div>

@endsection