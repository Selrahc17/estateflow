<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::with('propertyType')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('properties.index', compact('properties'));
    }

    public function create()
    {
        $propertyTypes = PropertyType::where('is_active', true)->get();
        return view('properties.create', compact('propertyTypes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_type_id' => 'required|exists:property_types,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'area_sqm' => 'nullable|numeric',
            'price' => 'required|numeric',
            'currency' => 'required|string|max:3',
            'status' => 'required|in:available,reserved,sold,under_construction',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'garage_spaces' => 'nullable|integer',
            'amenities' => 'nullable|array',
            'image_main' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $property = new Property();
        $property->property_type_id = $request->property_type_id;
        $property->title = $request->title;
        $property->description = $request->description;
        $property->location = $request->location;
        $property->latitude = $request->latitude;
        $property->longitude = $request->longitude;
        $property->area_sqm = $request->area_sqm;
        $property->price = $request->price;
        $property->currency = $request->currency;
        $property->status = $request->status;
        $property->bedrooms = $request->bedrooms;
        $property->bathrooms = $request->bathrooms;
        $property->garage_spaces = $request->garage_spaces;
        $property->amenities = $request->amenities;
        $property->is_featured = $request->is_featured ?? false;
        $property->is_active = $request->is_active ?? true;

        if ($request->hasFile('image_main')) {
            $image = $request->file('image_main');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/properties'), $imageName);
            $property->image_main = 'uploads/properties/' . $imageName;
        }

        $property->save();

        return redirect()->route('properties.index')
            ->with('success', 'Property created successfully.');
    }

    public function show($id)
    {
        $property = Property::with(['propertyType', 'reservations', 'payments', 'documents'])
            ->findOrFail($id);

        return view('properties.show', compact('property'));
    }

    public function edit($id)
    {
        $property = Property::findOrFail($id);
        $propertyTypes = PropertyType::where('is_active', true)->get();

        return view('properties.edit', compact('property', 'propertyTypes'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'property_type_id' => 'required|exists:property_types,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'area_sqm' => 'nullable|numeric',
            'price' => 'required|numeric',
            'currency' => 'required|string|max:3',
            'status' => 'required|in:available,reserved,sold,under_construction',
            'bedrooms' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'garage_spaces' => 'nullable|integer',
            'amenities' => 'nullable|array',
            'image_main' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $property = Property::findOrFail($id);
        $property->property_type_id = $request->property_type_id;
        $property->title = $request->title;
        $property->description = $request->description;
        $property->location = $request->location;
        $property->latitude = $request->latitude;
        $property->longitude = $request->longitude;
        $property->area_sqm = $request->area_sqm;
        $property->price = $request->price;
        $property->currency = $request->currency;
        $property->status = $request->status;
        $property->bedrooms = $request->bedrooms;
        $property->bathrooms = $request->bathrooms;
        $property->garage_spaces = $request->garage_spaces;
        $property->amenities = $request->amenities;
        $property->is_featured = $request->is_featured ?? false;
        $property->is_active = $request->is_active ?? true;

        if ($request->hasFile('image_main')) {
            $image = $request->file('image_main');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads/properties'), $imageName);
            $property->image_main = 'uploads/properties/' . $imageName;
        }

        $property->save();

        return redirect()->route('properties.index')
            ->with('success', 'Property updated successfully.');
    }

    public function destroy($id)
    {
        $property = Property::findOrFail($id);
        $property->delete();

        return redirect()->route('properties.index')
            ->with('success', 'Property deleted successfully.');
    }

    public function search(Request $request)
    {
        $query = Property::with('propertyType');

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('property_type_id')) {
            $query->where('property_type_id', $request->property_type_id);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('bedrooms')) {
            $query->where('bedrooms', '>=', $request->bedrooms);
        }

        if ($request->has('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $properties = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('properties.index', compact('properties'));
    }
}
