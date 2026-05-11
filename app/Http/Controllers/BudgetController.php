<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Project;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $query = Budget::with('project');

        if ($request->filled('search')) {
            $query->where('category', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $budgets           = $query->latest()->paginate(15)->withQueryString();
        $totalBudgets      = Budget::count();
        $totalEstimated    = Budget::sum('estimated_amount');
        $totalActual       = Budget::sum('actual_amount');
        $overBudgetCount   = Budget::whereColumn('actual_amount', '>', 'estimated_amount')->count();
        $projects          = Project::orderBy('name')->get();

        return view('budgets.index', compact(
            'budgets', 'totalBudgets', 'totalEstimated', 'totalActual', 'overBudgetCount', 'projects'
        ));
    }

    public function create(Request $request)
    {
        $projects        = Project::orderBy('name')->get();
        $selectedProject = $request->filled('project_id') ? $request->project_id : null;
        return view('budgets.create', compact('projects', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'category'         => 'required|string|max:255',
            'description'      => 'nullable|string',
            'estimated_amount' => 'required|numeric|min:0',
            'actual_amount'    => 'nullable|numeric|min:0',
            'currency'         => 'required|string|max:3',
            'budget_date'      => 'required|date',
            'status'           => 'required|in:planned,approved,in_progress,completed,over_budget',
            'notes'            => 'nullable|string',
        ]);

        Budget::create($request->all());

        $redirect = $request->filled('project_id')
            ? route('projects.show', $request->project_id)
            : route('budgets.index');

        return redirect($redirect)->with('success', 'Budget entry created successfully.');
    }

    public function show(Budget $budget)
    {
        $budget->load('project');
        return view('budgets.show', compact('budget'));
    }

    public function edit(Budget $budget)
    {
        $projects = Project::orderBy('name')->get();
        return view('budgets.edit', compact('budget', 'projects'));
    }

    public function update(Request $request, Budget $budget)
    {
        $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'category'         => 'required|string|max:255',
            'description'      => 'nullable|string',
            'estimated_amount' => 'required|numeric|min:0',
            'actual_amount'    => 'nullable|numeric|min:0',
            'currency'         => 'required|string|max:3',
            'budget_date'      => 'required|date',
            'status'           => 'required|in:planned,approved,in_progress,completed,over_budget',
            'notes'            => 'nullable|string',
        ]);

        $budget->update($request->all());

        return redirect()->route('budgets.index')->with('success', 'Budget entry updated successfully.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();
        return back()->with('success', 'Budget entry deleted successfully.');
    }
}
