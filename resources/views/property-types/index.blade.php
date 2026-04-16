@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2>Property Types</h2>
                    <a href="{{ route('property-types.create') }}" class="btn btn-primary">Add New Property Type</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Properties</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($propertyTypes as $propertyType)
                                    <tr>
                                        <td>{{ $propertyType->id }}</td>
                                        <td>{{ $propertyType->name }}</td>
                                        <td>{{ $propertyType->description ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $propertyType->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $propertyType->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>{{ $propertyType->properties->count() }}</td>
                                        <td>
                                            <a href="{{ route('property-types.show', $propertyType->id) }}" class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('property-types.edit', $propertyType->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('property-types.destroy', $propertyType->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No property types found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $propertyTypes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection