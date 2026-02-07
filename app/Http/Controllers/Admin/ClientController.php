<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\SlaPolicy;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::with('slaPolicy')->paginate(20);
        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        $slaPolicies = SlaPolicy::all();
        return view('admin.clients.create', compact('slaPolicies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clients',
            'email_domain' => 'nullable|string|max:255',
            'identifier_keywords' => 'nullable|string',
            'sla_policy_id' => 'nullable|exists:sla_policies,id',
        ]);

        Client::create($request->all());

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function edit(Client $client)
    {
        $slaPolicies = SlaPolicy::all();
        return view('admin.clients.edit', compact('client', 'slaPolicies'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clients,name,' . $client->id,
            'email_domain' => 'nullable|string|max:255',
            'identifier_keywords' => 'nullable|string',
            'sla_policy_id' => 'nullable|exists:sla_policies,id',
        ]);

        $client->update($request->all());

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        // Check for alerts dependent on this client? 
        // For now, let's allow delete but maybe we should nullify alerts.
        // Assuming cascade or set null on DB level, or just soft delete.
        // Standard delete for now.
        
        if ($client->alerts()->count() > 0) {
             return back()->with('error', 'Cannot delete Client with existing alerts.');
        }

        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
