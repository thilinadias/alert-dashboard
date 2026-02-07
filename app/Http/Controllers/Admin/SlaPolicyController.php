<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SlaPolicy;
use Illuminate\Http\Request;

class SlaPolicyController extends Controller
{
    public function index()
    {
        $policies = SlaPolicy::withCount('clients')->paginate(20);
        return view('admin.sla_policies.index', compact('policies'));
    }

    public function create()
    {
        return view('admin.sla_policies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sla_policies',
            'response_time_minutes' => 'required|integer|min:1',
            'resolution_time_minutes' => 'required|integer|min:1',
        ]);

        SlaPolicy::create($request->all());

        return redirect()->route('admin.sla-policies.index')
            ->with('success', 'SLA Policy created successfully.');
    }

    public function edit(SlaPolicy $slaPolicy)
    {
        return view('admin.sla_policies.edit', compact('slaPolicy'));
    }

    public function update(Request $request, SlaPolicy $slaPolicy)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sla_policies,name,' . $slaPolicy->id,
            'response_time_minutes' => 'required|integer|min:1',
            'resolution_time_minutes' => 'required|integer|min:1',
        ]);

        $slaPolicy->update($request->all());

        return redirect()->route('admin.sla-policies.index')
            ->with('success', 'SLA Policy updated successfully.');
    }

    public function destroy(SlaPolicy $slaPolicy)
    {
        if ($slaPolicy->clients()->count() > 0) {
            return back()->with('error', 'Cannot delete SLA Policy assigned to clients. Reassign clients first.');
        }

        $slaPolicy->delete();

        return redirect()->route('admin.sla-policies.index')
            ->with('success', 'SLA Policy deleted successfully.');
    }
}
