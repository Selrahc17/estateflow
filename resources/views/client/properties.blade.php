@extends('layouts.app')

@section('title', 'Browse Properties - EstateFlow')
@section('page-title', 'Browse Properties')
@section('page-subtitle', 'Available properties for you')

@section('content')
<div class="bg-white rounded-xl shadow-sm p-8 text-center text-gray-400">
    <i class="fas fa-building text-4xl mb-3 block text-gray-200"></i>
    <p>Browse available properties.</p>
    <a href="{{ route('properties.index') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
        View All Properties
    </a>
</div>
@endsection
