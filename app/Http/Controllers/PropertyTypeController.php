<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyTypeController extends Controller
{
    public function index()
    {
        $propertyTypes = PropertyType::orderBy('name')->paginate(10);
        return view('property-types.index', compact('propertyTypes'));
    }

    public function create()
    {
        return view('property-types.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:property_types',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $propertyType = new PropertyType();
        $propertyType->name = $request->name;
        $propertyType->description = $request->description;
        $propertyType->is_active = $request->is_active ?? true;
        $propertyType->save();

        return redirect()->route('property-types.index')
            ->with('success', 'Property type created successfully.');
    }

    public function show($id)
    {
        $propertyType = PropertyType::with('properties')->findOrFail($id);
        return view('property-types.show', compact('propertyType'));
    }

    public function edit($id)
    {
        $propertyType = PropertyType::findOrFail($id);
        return view('property-types.edit', compact('propertyType'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:property_types,name,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $propertyType = PropertyType::findOrFail($id);
        $propertyType->name = $request->name;
        $propertyType->description = $request->description;
        $propertyType->is_active = $request->is_active ?? true;
        $propertyType->save();

        return redirect()->route('property-types.index')
            ->with('success', 'Property type updated successfully.');
    }

    public function destroy($id)
    {
        $propertyType = PropertyType::findOrFail($id);
        $propertyType->delete();

        return redirect()->route('property-types.index')
            ->with('success', 'Property type deleted successfully.');
    }
}
