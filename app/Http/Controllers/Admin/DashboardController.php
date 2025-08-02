<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StatisticsFilterRequest;
use App\Models\Line;
use App\Models\OperatingDay;
use App\Models\PurchasedTicket;
use App\Models\Stop;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'statistics');
        $data = [];

        switch ($activeTab) {
            case 'statistics':
                $filterRequest = StatisticsFilterRequest::capture();
                $data = $this->getStatisticsData($filterRequest);
                break;
            case 'stops':
                $data['stops'] = Stop::withCount('lines')
                    ->orderBy('stop_name')
                    ->paginate(10);
                break;
            case 'lines':
                $data['lines'] = Line::orderBy('line_number')
                    ->paginate(10);
                break;
            case 'timetable':
                $data['trips'] = Trip::with(['line', 'operatingDay'])
                    ->orderBy('line_id')
                    ->paginate(10);
                break;
            case 'operatingdays':
                $data['operatingDays'] = OperatingDay::orderBy('day_id')
                    ->paginate(10);
                break;
            case 'users':
                $data['users'] = User::orderBy('user_id')
                    ->paginate(10);
                break;
            case 'tickets':
                $data['tickets'] = Ticket::orderBy('ticket_id')->paginate(10);
                break;
            case 'purchasedtickets':
                $data['purchasedTickets'] = PurchasedTicket::with(['ticket', 'user', 'transaction'])
                    ->orderByDesc('purchase_id')
                    ->paginate(10);
                break;
            case 'transactions':
                $data['transactions'] = Transaction::with(['user', 'fromStop', 'toStop', 'line'])
                    ->orderByDesc('transaction_id')
                    ->paginate(10);
                break;
        }

        return view('admin.dashboard', array_merge(['activeTab' => $activeTab], $data));
    }

    private function getStatisticsData(StatisticsFilterRequest $request): array
    {
        $totalUsers = User::count();
        $totalLines = Line::count();
        $totalStops = Stop::count();
        $totalTicketTypes = Ticket::count();
        $totalPurchasedTickets = PurchasedTicket::count();
        $totalTransactions = Transaction::count();
        $totalRevenue = Transaction::where('status', 'zakończona')->sum('price');

        $filterStartDate = $request->getFilterStartDate();
        $filterEndDate = $request->getFilterEndDate();

        $period = CarbonPeriod::create($filterStartDate, $filterEndDate);

        $dateLabels = [];
        $defaultDateValues = [];
        foreach ($period as $date) {
            $dateLabels[] = $date->format('d.m');
            $defaultDateValues[$date->toDateString()] = 0;
        }

        $ticketSalesRaw = PurchasedTicket::query()
            ->join('transactions', 'purchased_tickets.transaction_id', '=', 'transactions.transaction_id')
            ->where('transactions.status', 'zakończona')
            ->whereBetween('purchased_tickets.created_at', [$filterStartDate, $filterEndDate])
            ->selectRaw('DATE(purchased_tickets.created_at) as sale_date, COUNT(*) as count')
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'asc')
            ->pluck('count', 'sale_date')
            ->all();

        $ticketSalesData = array_values(array_merge($defaultDateValues, $ticketSalesRaw));

        $userRegistrationsRaw = User::query()
            ->whereBetween('created_at', [$filterStartDate, $filterEndDate])
            ->selectRaw('DATE(created_at) as registration_date, COUNT(*) as count')
            ->groupBy('registration_date')
            ->orderBy('registration_date', 'asc')
            ->pluck('count', 'registration_date')
            ->all();

        $userRegistrationData = array_values(array_merge($defaultDateValues, $userRegistrationsRaw));

        $popularTicketTypes = Ticket::query()
            ->withCount(['purchasedTickets' => function ($query) use ($filterStartDate, $filterEndDate) {
                $query->join('transactions', 'purchased_tickets.transaction_id', '=', 'transactions.transaction_id')
                    ->where('transactions.status', 'zakończona')
                    ->whereBetween('purchased_tickets.created_at', [$filterStartDate, $filterEndDate]);
            }])
            ->orderByDesc('purchased_tickets_count')
            ->get();

        $popularTicketLabels = $popularTicketTypes->pluck('ticket_name')->all();
        $popularTicketData = $popularTicketTypes->pluck('purchased_tickets_count')->all();

        return compact(
            'totalUsers',
            'totalLines',
            'totalStops',
            'totalTicketTypes',
            'totalPurchasedTickets',
            'totalTransactions',
            'totalRevenue',
            'dateLabels',
            'ticketSalesData',
            'userRegistrationData',
            'popularTicketLabels',
            'popularTicketData',
            'filterStartDate',
            'filterEndDate'
        );
    }
}
