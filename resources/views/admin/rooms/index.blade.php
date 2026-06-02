@extends('admin.layout')

@section('title', 'Manage 3D Saves')
@section('page-title', 'Manage 3D Saves')

@section('content')
<div class="bg-card rounded-[14px] border border-border/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-muted/50 border-b border-border/10">
                    <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-paragraph font-medium">Room ID</th>
                    <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-paragraph font-medium">User</th>
                    <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-paragraph font-medium">Name</th>
                    <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-paragraph font-medium">Dimensions</th>
                    <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-paragraph font-medium">Created At</th>
                    <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-paragraph font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/10">
                @if(isset($fromFlask) && $fromFlask)
                    @forelse($flaskRooms as $room)
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-foreground">#{{ substr($room['id'], 0, 8) }}</td>
                        <td class="px-6 py-4 text-sm text-foreground">
                            User #{{ $room['user_id'] ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-foreground">{{ $room['name'] ?? 'Unnamed' }}</td>
                        <td class="px-6 py-4 text-sm text-paragraph">
                            {{ $room['width'] ?? '?' }} × {{ $room['length'] ?? '?' }} × {{ $room['height'] ?? '?' }}m
                        </td>
                        <td class="px-6 py-4 text-sm text-paragraph">
                            {{ isset($room['created_at']) ? \Carbon\Carbon::parse($room['created_at'])->format('Y-m-d H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-xs text-muted-foreground">Flask Server</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-paragraph text-sm">No 3D saves found.</td>
                    </tr>
                    @endforelse
                @else
                    @forelse($rooms as $room)
                    <tr class="hover:bg-muted/30 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-foreground">#{{ $room->id }}</td>
                        <td class="px-6 py-4 text-sm text-foreground">
                            {{ $room->user?->username ?? 'Unknown' }}
                            <div class="text-xs text-paragraph">{{ $room->user?->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-foreground">{{ $room->name }}</td>
                        <td class="px-6 py-4 text-sm text-paragraph">{{ $room->width }} × {{ $room->length }} × {{ $room->height }}m</td>
                        <td class="px-6 py-4 text-sm text-paragraph">{{ $room->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('room.editor', $room->id) }}"
                               class="inline-flex items-center justify-center rounded-lg bg-primary/10 text-primary px-3 py-1.5 text-xs font-medium hover:bg-primary/20 transition-colors"
                               target="_blank">View 3D</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-paragraph text-sm">No 3D saves found.</td>
                    </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
