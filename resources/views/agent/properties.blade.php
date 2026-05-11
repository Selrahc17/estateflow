@extends('layouts.app')

@section('title', 'Properties - EstateFlow')
@section('page-title', 'Properties')
@section('page-subtitle', 'Manage your listings')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
    <i class="fas fa-building text-4xl mb-3 block text-gray-200"></i>
    <p>Your property listings.</p>
    <a href="{{ route('properties.index') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
        View All Properties
    </a>
</div>
@endsection
