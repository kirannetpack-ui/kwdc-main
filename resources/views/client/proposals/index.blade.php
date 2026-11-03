@extends('layouts.app')

@section('title', 'My Proposals')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Proposals</h2>
        @if(isset($pendingRequests) && $pendingRequests->count() > 0)
            <a href="{{ route('client.proposals.create', $pendingRequests->first()->id) }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New Proposal
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card stats-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stats-label">Total Proposals</h6>
                            <h2 class="stats-number">{{ $proposals->count() }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card stats-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stats-label">Pending</h6>
                            <h2 class="stats-number">{{ $proposals->where('status', 'pending')->count() }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card stats-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stats-label">Accepted</h6>
                            <h2 class="stats-number">{{ $proposals->where('status', 'accepted')->count() }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card stats-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stats-label">Rejected</h6>
                            <h2 class="stats-number">{{ $proposals->where('status', 'rejected')->count() }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Proposals List -->
    @if($proposals->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Proposals</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Warehouse</th>
                                <th>Proposed Price</th>
                                <th>Valid Until</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proposals as $proposal)
                            <tr>
                                <td>#{{ $proposal->id }}</td>
                                <td>
                                    <a href="{{ route('warehouses.show', $proposal->warehouse_id) }}">
                                        {{ $proposal->warehouse->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>रु {{ number_format($proposal->proposed_price, 2) }}</td>
                                <td>
                                    @if($proposal->valid_until)
                                        {{ \Carbon\Carbon::parse($proposal->valid_until)->format('M d, Y') }}
                                        @if(\Carbon\Carbon::parse($proposal->valid_until)->isPast())
                                            <span class="badge bg-danger">Expired</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $proposal->status_badge ?? ($proposal->status === 'pending' ? 'warning' : ($proposal->status === 'accepted' ? 'success' : 'danger')) }}">
                                        {{ ucfirst($proposal->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if($proposal->status == 'pending')
                                            <form method="POST" action="{{ route('client.proposals.accept', $proposal->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success" onclick="return confirm('Accept this proposal?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('client.proposals.reject', $proposal->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this proposal?')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#negotiateModal{{ $proposal->id }}">
                                                <i class="fas fa-handshake"></i>
                                            </button>
                                        @else
                                            <span class="text-muted">No actions</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Negotiate Modal -->
                            <div class="modal fade" id="negotiateModal{{ $proposal->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Negotiate Proposal #{{ $proposal->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('client.proposals.negotiate', $proposal->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Current Proposed Price</label>
                                                    <input type="text" class="form-control" value="रु {{ number_format($proposal->proposed_price, 2) }}" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Your Counter Offer (रु) <span class="text-danger">*</span></label>
                                                    <input type="number" name="counter_price" class="form-control" 
                                                           step="0.01" min="0" required 
                                                           placeholder="Enter your counter offer">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="3" 
                                                              placeholder="Explain your counter offer..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Send Negotiation</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($proposals, 'links'))
                <div class="card-footer">
                    {{ $proposals->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Empty State -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-contract text-muted" style="font-size: 64px;"></i>
                <h4 class="mt-4">No Proposals Found</h4>
                <p class="text-muted">You haven't created any proposals yet.</p>
                @if(isset($pendingRequests) && $pendingRequests->count() > 0)
                    <a href="{{ route('client.proposals.create', $pendingRequests->first()->id) }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Create Your First Proposal
                    </a>
                @else
                    <p class="text-muted mt-3">You need to have a pending warehouse request to create a proposal.</p>
                    <a href="{{ route('my-requests.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus me-2"></i>Create a Request
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* Stats Cards */
    .stats-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    
    .stats-card .stats-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    
    .stats-card .stats-number {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    
    .stats-card .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    
    .stats-card.stats-primary .stats-icon {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }
    
    .stats-card.stats-success .stats-icon {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }
    
    .stats-card.stats-warning .stats-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    
    .stats-card.stats-danger .stats-icon {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    /* Table styles */
    .table th {
        font-weight: 600;
        color: #475569;
        border-top: none;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        padding: 4px 8px;
        font-size: 12px;
    }
    
    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 12px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    });
</script>
@endpush