<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;

class AdminTicketController extends Controller
{
    public function create()
    {
        return view('admin.tickets.create');
    }

    public function store(StoreTicketRequest $request)
    {
        try {
            $validatedData = $request->validated();

            Ticket::create([
                'ticket_name' => $validatedData['ticket_name'],
                'description' => $validatedData['description'] ?? null,
                'price' => $validatedData['price'],
                'validity_hours' => $validatedData['validity_hours'],
                'is_active' => $request->boolean('is_active'),
            ]);

            return redirect()->route('dashboard', ['tab' => 'tickets'])
                ->with('success', 'Bilet został dodany.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('unique_error', 'Bilet o tej nazwie już istnieje.');
            }
            return back()->withInput()->with('error', 'Wystąpił błąd: ' . $e->getMessage());
        }
    }

    public function edit(Ticket $ticket)
    {
        return view('admin.tickets.edit', ['ticket' => $ticket]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        try {
            $validatedData = $request->validated();

            $ticket->update([
                'ticket_name' => $validatedData['ticket_name'],
                'description' => $validatedData['description'] ?? null,
                'price' => $validatedData['price'],
                'validity_hours' => $validatedData['validity_hours'],
                'is_active' => $request->boolean('is_active'),
            ]);

            return redirect()->route('dashboard', ['tab' => 'tickets'])
                ->with('success', 'Bilet został zaktualizowany.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return back()->withInput()->with('unique_error', 'Bilet o tej nazwie już istnieje.');
            }
            return back()->withInput()->with('error', 'Wystąpił błąd: ' . $e->getMessage());
        }
    }

    public function destroy(Ticket $ticket)
    {
        try {
            $ticket->delete();
            return redirect()->route('dashboard', ['tab' => 'tickets'])
                ->with('success', 'Bilet został usunięty.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard', ['tab' => 'tickets'])
                ->with('error', $e->getMessage());
        }
    }
}
