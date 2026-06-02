@extends('admin.layout')
@section('title', 'Manage API — RenovaSim Admin')
@section('page-title', 'Manage API')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- API Key Card --}}
    <div class="bg-card rounded-2xl border border-border/10 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-serif text-lg text-foreground">API Key</h2>
                <p class="text-sm text-paragraph mt-1">Gunakan sebagai <code class="bg-muted px-1.5 py-0.5 rounded text-xs">Authorization: Bearer &lt;key&gt;</code></p>
            </div>
            <form method="POST" action="{{ route('admin.api.regenerate') }}" onsubmit="return confirm('Regenerate API key? Key lama akan tidak valid.')">
                @csrf
                <button type="submit" class="text-xs font-medium bg-primary text-primary-foreground px-4 py-2 rounded-xl hover:opacity-90 transition-opacity">
                    Regenerate Key
                </button>
            </form>
        </div>

        <div class="mt-4 relative">
            @if($apiKey)
                <div class="flex items-center gap-3 bg-muted/60 border border-border/20 rounded-xl px-4 py-3" x-data="{ show: false }">
                    <code class="flex-1 text-sm font-mono text-foreground break-all" x-text="show ? '{{ $apiKey }}' : '{{ Str::mask($apiKey, '*', 8) }}'"></code>
                    <button type="button" @click="show = !show" class="text-paragraph hover:text-foreground text-xs shrink-0" x-text="show ? 'Hide' : 'Show'"></button>
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $apiKey }}');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500)"
                        class="text-paragraph hover:text-foreground text-xs shrink-0">Copy</button>
                </div>
            @else
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/40 rounded-xl px-4 py-3">
                    <p class="text-sm text-amber-700 dark:text-amber-400">Belum ada API key. Klik <strong>Regenerate Key</strong> untuk membuat key baru.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Endpoints Table --}}
    <div class="bg-card rounded-2xl border border-border/10 p-6">
        <h2 class="font-serif text-lg text-foreground mb-4">Endpoints Tersedia</h2>
        <p class="text-sm text-paragraph mb-5">Base URL: <code class="bg-muted px-1.5 py-0.5 rounded text-xs break-all">{{ $baseUrl }}</code></p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border/10">
                        <th class="text-left text-xs uppercase tracking-wider text-paragraph pb-3 font-sans pr-4">Method</th>
                        <th class="text-left text-xs uppercase tracking-wider text-paragraph pb-3 font-sans pr-4">Endpoint</th>
                        <th class="text-left text-xs uppercase tracking-wider text-paragraph pb-3 font-sans">Deskripsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/5">
                    @foreach($endpoints as $ep)
                    <tr class="group">
                        <td class="py-3 pr-4">
                            <span class="inline-block text-[11px] font-mono font-semibold px-2 py-0.5 rounded
                                {{ $ep['method'] === 'GET' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' }}">
                                {{ $ep['method'] }}
                            </span>
                        </td>
                        <td class="py-3 pr-4">
                            <code class="text-xs text-foreground font-mono">{{ $ep['path'] }}</code>
                        </td>
                        <td class="py-3 text-paragraph text-sm">{{ $ep['desc'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Usage Example --}}
    <div class="bg-card rounded-2xl border border-border/10 p-6">
        <h2 class="font-serif text-lg text-foreground mb-4">Contoh Penggunaan</h2>

        <div class="space-y-4">
            <div>
                <p class="text-xs uppercase tracking-wider text-paragraph mb-2 font-sans">cURL</p>
                <pre class="bg-muted/60 rounded-xl px-4 py-3 text-xs font-mono text-foreground overflow-x-auto">curl -H "Authorization: Bearer &lt;API_KEY&gt;" \
     {{ $baseUrl }}/users</pre>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wider text-paragraph mb-2 font-sans">JavaScript (fetch)</p>
                <pre class="bg-muted/60 rounded-xl px-4 py-3 text-xs font-mono text-foreground overflow-x-auto">const res = await fetch('{{ $baseUrl }}/projects', {
  headers: { 'Authorization': 'Bearer &lt;API_KEY&gt;' }
});
const { data } = await res.json();</pre>
            </div>

            <div>
                <p class="text-xs uppercase tracking-wider text-paragraph mb-2 font-sans">Flutter / Dart</p>
                <pre class="bg-muted/60 rounded-xl px-4 py-3 text-xs font-mono text-foreground overflow-x-auto">final res = await http.get(
  Uri.parse('{{ $baseUrl }}/materials'),
  headers: {'Authorization': 'Bearer &lt;API_KEY&gt;'},
);</pre>
            </div>
        </div>
    </div>

    {{-- Query params --}}
    <div class="bg-card rounded-2xl border border-border/10 p-6">
        <h2 class="font-serif text-lg text-foreground mb-4">Query Parameters</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border/10">
                        <th class="text-left text-xs uppercase tracking-wider text-paragraph pb-3 font-sans pr-4">Endpoint</th>
                        <th class="text-left text-xs uppercase tracking-wider text-paragraph pb-3 font-sans pr-4">Parameter</th>
                        <th class="text-left text-xs uppercase tracking-wider text-paragraph pb-3 font-sans">Contoh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/5">
                    <tr><td class="py-2 pr-4 font-mono text-xs">/projects</td><td class="py-2 pr-4 font-mono text-xs">user_id</td><td class="py-2 text-paragraph text-xs">/projects?user_id=2</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">/estimations</td><td class="py-2 pr-4 font-mono text-xs">project_id, user_id</td><td class="py-2 text-paragraph text-xs">/estimations?project_id=5</td></tr>
                    <tr><td class="py-2 pr-4 font-mono text-xs">/users/{id}</td><td class="py-2 pr-4 font-mono text-xs">&mdash;</td><td class="py-2 text-paragraph text-xs">/users/2</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Response format --}}
    <div class="bg-card rounded-2xl border border-border/10 p-6">
        <h2 class="font-serif text-lg text-foreground mb-3">Response Format</h2>
        <pre class="bg-muted/60 rounded-xl px-4 py-3 text-xs font-mono text-foreground overflow-x-auto">{
  "data": [ ... ],   // array of objects
  "total": 42        // total count
}</pre>
    </div>

</div>
@endsection
