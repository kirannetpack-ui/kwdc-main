<x-app-layout>
    <x-slot name="header"><h2>Manage Equipment Jobs</h2></x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded shadow">
                <table class="w-full border">
                    <thead><tr><th>Request #</th><th>Equipment Notes</th><th>Assigned Equipment</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach($jobs as $job)
                        <tr>
                            <td>{{ $job->warehouse_request_id }}</td>
                            <td>{{ $job->notes }}</td>
                            <td>{{ $job->equipment->type ?? 'Not assigned' }}</td>
                            <td>{{ ucfirst($job->status) }}</td>
                            <td>
                                @if(!$job->equipment_id)
                                    <form method="POST" action="{{ route('admin.equipment.jobs.assign', $job->id) }}">
                                        @csrf
                                        <select name="equipment_id" required>
                                            <option value="">Select equipment</option>
                                            @foreach($equipment as $eq)
                                                <option value="{{ $eq->id }}">{{ $eq->type }} ({{ $eq->model }})</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded">Assign</button>
                                    </form>
                                @else
                                    <span>Assigned to: {{ $job->equipment->type }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>s