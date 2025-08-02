<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePurchasedTicketRequest;
use App\Http\Requests\StorePurchasedTicketRequest;
use App\Models\PurchasedTicket;
use App\Models\Ticket;
use App\Models\User;

class AdminPurchasedTicketController extends Controller
{
    public function index()
    {
        return redirect()->route('dashboard', ['tab' => 'purchasedtickets'])->with('info', 'Zarządzanie zakupionymi biletami odbywa się poprzez główny panel.');
    }

    public function create()
    {
        $tickets = Ticket::where('is_active', true)->orderBy('ticket_name')->get();
        $users = User::orderBy('name')->get();
        return view('admin.purchasedtickets.create', compact('tickets', 'users'));
    }

    public function store(StorePurchasedTicketRequest $request)
    {
        try {
            PurchasedTicket::create($request->validated());
            return redirect()->route('dashboard', ['tab' => 'purchasedtickets'])
                ->with('success', 'Zakupiony bilet został dodany pomyślnie.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Wystąpił błąd podczas dodawania biletu: ' . $e->getMessage());
        }
    }

    public function show(PurchasedTicket $purchasedTicket)
    {
        $purchasedTicket->load(['ticket', 'user', 'transaction.fromStop', 'transaction.toStop', 'transaction.line']);
        return view('admin.purchasedtickets.show', ['purchasedTicket' => $purchasedTicket]);
    }

    public function edit(PurchasedTicket $purchasedTicket)
    {
        $tickets = Ticket::where('is_active', true)->orderBy('ticket_name')->get();
        $users = User::orderBy('name')->get();
        return view('admin.purchasedtickets.edit', compact('purchasedTicket', 'tickets', 'users'));
    }

    public function update(UpdatePurchasedTicketRequest $request, PurchasedTicket $purchasedTicket)
    {
        try {
            $purchasedTicket->update($request->validated());
            return redirect()->route('dashboard', ['tab' => 'purchasedtickets'])
                ->with('success', 'Zakupiony bilet został zaktualizowany pomyślnie.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Wystąpił błąd podczas aktualizacji biletu: ' . $e->getMessage());
        }
    }

    public function destroy(PurchasedTicket $purchasedTicket)
    {
        try {
            $purchasedTicket->delete();
            return redirect()->route('dashboard', ['tab' => 'purchasedtickets'])
                ->with('success', 'Zakupiony bilet został usunięty pomyślnie.');
        } catch (\Exception $e) {
            return back()->with('error', 'Wystąpił błąd podczas usuwania biletu: ' . $e->getMessage());
        }
    }
}
