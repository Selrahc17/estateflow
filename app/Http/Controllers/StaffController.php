<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = Staff::with('user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $contractors      = $query->latest()->paginate(15)->withQueryString();
        $totalContractors = Staff::count();
        $activeCount      = Staff::where('status', 'active')->count();
        $inactiveCount    = Staff::where('status', 'inactive')->count();

        return view('contractors.index', compact('contractors', 'totalContractors', 'activeCount', 'inactiveCount'));
    }

    public function create()
    {
        $users = User::where('role', 'staff')->where('is_active', true)->get();
        return view('contractors.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name'   => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'required|string|max:20',
            'phone_alt'      => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'license_number' => 'nullable|string|max:100',
            'tax_id'         => 'nullable|string|max:100',
            'type'           => 'required|in:general_contractor,subcontractor,supplier,consultant',
            'specialization' => 'nullable|string',
            'status'         => 'required|in:active,inactive,blacklisted',
            'notes'          => 'nullable|string',
            'user_id'        => 'nullable|exists:users,id',
        ]);

        Staff::create($request->all());

        return redirect()->route('contractors.index')->with('success', 'Staff member added successfully.');
    }

    public function show(Staff $contractor)
    {
        $contractor->load(['projects', 'resources']);
        return view('contractors.show', compact('contractor'));
    }

    public function edit(Staff $contractor)
    {
        $users = User::where('role', 'staff')->where('is_active', true)->get();
        return view('contractors.edit', compact('contractor', 'users'));
    }

    public function update(Request $request, Staff $contractor)
    {
        $request->validate([
            'company_name'   => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'required|string|max:20',
            'phone_alt'      => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'license_number' => 'nullable|string|max:100',
            'tax_id'         => 'nullable|string|max:100',
            'type'           => 'required|in:general_contractor,subcontractor,supplier,consultant',
            'specialization' => 'nullable|string',
            'status'         => 'required|in:active,inactive,blacklisted',
            'notes'          => 'nullable|string',
            'user_id'        => 'nullable|exists:users,id',
        ]);

        $contractor->update($request->all());

        return redirect()->route('contractors.index')->with('success', 'Staff member updated successfully.');
    }

    public function destroy(Staff $contractor)
    {
        $contractor->delete();
        return back()->with('success', "Staff member \"{$contractor->company_name}\" deleted successfully.");
    }
}
