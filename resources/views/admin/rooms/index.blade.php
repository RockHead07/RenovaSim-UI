@extends('admin.layout')

@section('title', 'Manage 3D Saves')
@section('page-title', 'Manage 3D Saves')

@section('content')
<div
    x-data="{
        deleteModal: false,
        deleteUrl: '',
        deleteSource: '',
        roomName: '',
        openDelete(url, source, name) {
            this.deleteUrl = url;
            this.deleteSource = source;
            this.roomName = name;
            this.deleteModal = true;
        }
    }"
>

    {{-- Header bar --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-paragraph">
            Total:
            <span class="font-medium text-foreground">
                {{ isset($fromFlask) && $fromFlask ? count($flaskRooms ?? []) : count($rooms ?? []) }}
            </span>
            saved rooms
        </p>
        @if(isset($fromFlask) && $fromFlask)
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-medium" style="background:rgba(139,160,35,0.12);color:#8BA023">
                <span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span>
                Source: Flask 3D Server
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-medium" style="background:rgba(99,120,200,0.12);color:#8899dd">
                <span class="w-1.5 h-1.5 rounded-full bg-current inline-block"></span>
                Source: Database
            </span>
        @endif
    </div>

    {{-- Table --}}
    <div class="bg-card rounded-[14px] border border-border/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-muted/50 border-b border-border/10">
                        <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-paragraph font-medium">Room ID</th>
                        <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-paragraph font-medium">User</th>
                        <th class="px-6 py-4 text-[10px] uppercase tracking-widest text-paragraph font-medium">Owner</th>
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
                            <td class="px-6 py-4 text-sm font-mono font-medium text-foreground">
                                #{{ substr($room['id'], 0, 8) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-foreground">
                                <span class="font-medium">{{ $room['username'] ?? 'Unknown' }}</span>
                                @if(!empty($room['email']))
                                    <div class="text-xs text-paragraph">{{ $room['email'] }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-paragraph">
                                {{ $room['full_name'] ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-foreground">
                                {{ $room['name'] ?? 'Unnamed' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-paragraph">
                                {{ $room['width'] ?? '?' }} × {{ $room['length'] ?? '?' }} × {{ $room['height'] ?? '?' }}m
                            </td>
                            <td class="px-6 py-4 text-sm text-paragraph">
                                {{ isset($room['created_at']) ? \Carbon\Carbon::parse($room['created_at'])->format('Y-m-d H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="http://localhost:5000" target="_blank"
                                       class="inline-flex items-center justify-center rounded-lg bg-primary/10 text-primary px-3 py-1.5 text-xs font-medium hover:bg-primary/20 transition-colors">
                                        View 3D
                                    </a>
                                    <button
                                        type="button"
                                        @click="openDelete('{{ route('admin.rooms.destroy', $room['id']) }}', 'flask', '{{ addslashes($room['name'] ?? 'Unnamed') }}')"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                                        style="background:rgba(220,50,50,0.10);color:rgb(239,68,68)"
                                        onmouseover="this.style.background='rgba(220,50,50,0.20)'"
                                        onmouseout="this.style.background='rgba(220,50,50,0.10)'"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-paragraph text-sm">
                                <svg class="w-8 h-8 mx-auto mb-2 opacity-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                                No 3D saves found.
                            </td>
                        </tr>
                        @endforelse

                    @else
                        @forelse($rooms as $room)
                        <tr class="hover:bg-muted/30 transition-colors">
                            <td class="px-6 py-4 text-sm font-mono font-medium text-foreground">#{{ $room->id }}</td>
                            <td class="px-6 py-4 text-sm text-foreground">
                                <span class="font-medium">{{ $room->user?->username ?? 'Unknown' }}</span>
                                <div class="text-xs text-paragraph">{{ $room->user?->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-paragraph">
                                {{ trim(($room->user?->first_name ?? '') . ' ' . ($room->user?->last_name ?? '')) ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-foreground">{{ $room->name }}</td>
                            <td class="px-6 py-4 text-sm text-paragraph">
                                {{ $room->width }} × {{ $room->length }} × {{ $room->height }}m
                            </td>
                            <td class="px-6 py-4 text-sm text-paragraph">{{ $room->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('room.editor', $room->id) }}" target="_blank"
                                       class="inline-flex items-center justify-center rounded-lg bg-primary/10 text-primary px-3 py-1.5 text-xs font-medium hover:bg-primary/20 transition-colors">
                                        View 3D
                                    </a>
                                    <button
                                        type="button"
                                        @click="openDelete('{{ route('admin.rooms.destroy', $room->id) }}', 'db', '{{ addslashes($room->name) }}')"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                                        style="background:rgba(220,50,50,0.10);color:rgb(239,68,68)"
                                        onmouseover="this.style.background='rgba(220,50,50,0.20)'"
                                        onmouseout="this.style.background='rgba(220,50,50,0.10)'"
                                    >
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-paragraph text-sm">
                                <svg class="w-8 h-8 mx-auto mb-2 opacity-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                                No 3D saves found.
                            </td>
                        </tr>
                        @endforelse
                    @endif

                </tbody>
            </table>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div
        x-show="deleteModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
        x-cloak
        @keydown.escape.window="deleteModal = false"
    >
        <div
            x-show="deleteModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-card border border-border/10 rounded-[16px] shadow-2xl p-6 w-full max-w-sm mx-4"
            @click.outside="deleteModal = false"
        >
            {{-- Icon --}}
            <div class="w-11 h-11 rounded-full flex items-center justify-center mb-4" style="background:rgba(220,50,50,0.12)">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="rgb(239,68,68)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                </svg>
            </div>

            <h3 class="text-foreground font-serif text-lg mb-1">Hapus Hasil Save?</h3>
            <p class="text-paragraph text-sm mb-1">
                Data 3D untuk room <span class="text-foreground font-medium" x-text="'&quot;' + roomName + '&quot;'"></span> akan dihapus permanen.
            </p>
            <p class="text-paragraph text-xs mb-6">Tindakan ini tidak dapat dibatalkan.</p>

            <div class="flex gap-2 justify-end">
                <button
                    type="button"
                    @click="deleteModal = false"
                    class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium bg-muted hover:bg-muted/80 text-foreground transition-colors"
                >
                    Batal
                </button>
                <form :action="deleteUrl" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="source" :value="deleteSource">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition-colors text-white"
                        style="background:rgb(220,50,50)"
                        onmouseover="this.style.background='rgb(185,28,28)'"
                        onmouseout="this.style.background='rgb(220,50,50)'"
                    >
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                        </svg>
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
