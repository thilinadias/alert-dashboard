<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassificationRule;
use App\Models\Client;
use Illuminate\Http\Request;

class ClassificationRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rules = ClassificationRule::orderBy('priority')->get();
        return view('admin.classification-rules.index', compact('rules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::all();
        return view('admin.classification-rules.create', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'rule_type' => 'required|in:subject,body,sender',
            'target_severity' => 'required|in:critical,warning,info,default',
            'target_client_id' => 'nullable|exists:clients,id',
            'priority' => 'required|integer',
        ]);

        ClassificationRule::create($validated);

        return redirect()->route('admin.classification-rules.index')
            ->with('success', 'Rule created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassificationRule $classificationRule)
    {
        $clients = Client::all();
        return view('admin.classification-rules.edit', compact('classificationRule', 'clients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassificationRule $classificationRule)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'rule_type' => 'required|in:subject,body,sender',
            'target_severity' => 'required|in:critical,warning,info,default',
            'target_client_id' => 'nullable|exists:clients,id',
            'priority' => 'required|integer',
        ]);

        $classificationRule->update($validated);

        return redirect()->route('admin.classification-rules.index')
            ->with('success', 'Rule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassificationRule $classificationRule)
    {
        $classificationRule->delete();

        return redirect()->route('admin.classification-rules.index')
            ->with('success', 'Rule deleted successfully.');
    }

    /**
     * Reorder rules.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:classification_rules,id',
        ]);

        foreach ($request->ids as $index => $id) {
            ClassificationRule::where('id', $id)->update(['priority' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }
}
