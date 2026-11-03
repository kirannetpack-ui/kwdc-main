<?php

namespace App\Http\Controllers;

use App\Models\UserReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['month' => ['nullable', 'date_format:Y-m-d']]);
        $month = ($request->date('month') ?? now())->startOfMonth();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        $reminders = UserReminder::where('user_id', Auth::id())
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at')
            ->get();

        $upcomingReminders = UserReminder::where('user_id', Auth::id())
            ->where('starts_at', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        return view('reminders.index', compact('reminders', 'upcomingReminders', 'month'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedReminder($request);
        $data['user_id'] = Auth::id();

        UserReminder::create($data);

        return redirect()->route('reminders.index')->with('success', 'Reminder created.');
    }

    public function update(Request $request, UserReminder $reminder)
    {
        abort_unless($reminder->user_id === Auth::id(), 403);

        $reminder->update($this->validatedReminder($request));

        return redirect()->route('reminders.index')->with('success', 'Reminder updated.');
    }

    public function destroy(UserReminder $reminder)
    {
        abort_unless($reminder->user_id === Auth::id(), 403);

        $reminder->delete();

        return redirect()->route('reminders.index')->with('success', 'Reminder deleted.');
    }

    private function validatedReminder(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['required', 'date'],
            'remind_at' => ['nullable', 'date', 'before_or_equal:starts_at'],
        ]);

        $data['status'] = 'scheduled';

        return $data;
    }
}
