@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>{{ $propertyType->name }}</h2>
                    <div>
                        <a href="{{ route('property-types.edit', $propertyType->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('property-types.destroy', $propertyType->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <p><strong>ID:</strong> {{ $propertyType->id }}</p>
                            <p><strong>Name:</strong> {{ $propertyType->name }}</p>
                            <p><strong>Description:</strong> {{ $propertyType->description ?? 'N/A' }}</p>
                            <p><strong>Status:</strong>
                                <span class="badge {{ $propertyType->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $propertyType->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5>Statistics</h5>
                            <p><strong>Total Properties:</strong> {{ $propertyType->properties->count() }}</p>
                            <p><strong>Created At:</strong> {{ $propertyType->created_at->format('M d, Y h:i A') }}</p>
                            <p><strong>Updated At:</strong> {{ $propertyType->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>

                    @if ($propertyType->properties->count() > 0)
                        <hr>
                        <h5>Associated Properties</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($propertyType->properties as $property)
                                        <tr>
                                            <td>{{ $property->id }}</td>
                                            <td>{{ $property->title }}</td>
                                            <td>{{ $property->property_type->name }}</td>
                                            <td>
                                                <span class="badge {{ $property->is_available ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $property->is_available ? 'Available' : 'Reserved' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('property-types.index') }}" class="btn btn-secondary">Back to Property Types</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection