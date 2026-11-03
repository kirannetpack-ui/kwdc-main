@extends('layouts.app')

@section('title', 'Notifications')
@section('header', 'Notifications')

@push('styles')
<style>
    .kwdc-glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03);
    }
    .kwdc-notif-item {
        transition: all 0.15s ease;
    }
    .kwdc-notif-item:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="kwdc-glass-card p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-base flex-shrink-0">
                <i class="fas fa-bell"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Notifications</h1>
                    @if($unreadCount > 0)
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">
                            {{ $unreadCount }} new
                        </span>
                    @else
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            All Caught Up
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-0.5">System updates, dispatch events, and reminder alerts.</p>
            </div>
        </div>

        @if($unreadCount > 0)
        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5 whitespace-nowrap">
                <i class="fas fa-check-double text-orange-400 text-xs"></i>
                <span>Mark All Read</span>
            </button>
        </form>
        @endif
    </div>

    <!-- Notifications List -->
    <div class="kwdc-glass-card p-6 space-y-3">
        @if($notifications->count() > 0)
            <div class="space-y-2.5">
                @foreach($notifications as $notification)
                    <div class="p-4 rounded-2xl border transition kwdc-notif-item flex items-start justify-between gap-4 {{ !$notification->is_read ? 'bg-orange-50/25 border-orange-200/60' : 'bg-white border-slate-100' }}">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs flex-shrink-0 mt-0.5
                                {{ !$notification->is_read ? 'bg-orange-500 text-white shadow-sm' : 'bg-slate-100 text-slate-500' }}">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-900 text-xs truncate">{{ $notification->title }}</h3>
                                    @if(!$notification->is_read)
                                        <span class="w-2 h-2 rounded-full bg-orange-500 flex-shrink-0"></span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $notification->message }}</p>
                                <p class="text-[11px] text-slate-400 font-medium">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 flex-shrink-0">
                            @if(!$notification->is_read)
                                <button onclick="markAsRead({{ $notification->id }})" class="w-7 h-7 rounded-lg hover:bg-emerald-50 text-slate-400 hover:text-emerald-600 flex items-center justify-center text-xs transition" title="Mark as read">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                            <button onclick="deleteNotification({{ $notification->id }})" class="w-7 h-7 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 flex items-center justify-center text-xs transition" title="Delete notification">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($notifications->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <div class="py-12 text-center text-slate-400 space-y-2">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center mx-auto text-xl">
                    <i class="far fa-bell-slash"></i>
                </div>
                <h3 class="font-bold text-slate-700 text-sm">Inbox Zero</h3>
                <p class="text-xs">You have no unread alerts at this time.</p>
            </div>
        @endif
    </div>
</div>

<script>
function markAsRead(id) {
    fetch(`/notifications/${id}/mark-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(id) {
    if (confirm('Delete this notification?')) {
        fetch(`/notifications/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endsection
