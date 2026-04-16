@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="space-y-4" x-data="dashboardPage()">

    {{-- Welcome --}}
    <div>
        <h1 class="text-2xl font-serif text-foreground">Welcome back 👋</h1>
        <p class="text-sm text-paragraph mt-1" x-text="formattedDate"></p>
    </div>

    {{-- ════════════════════════ TOP STATS ROW ════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Total Users --}}
        <div class="relative bg-card rounded-[14px] border border-border/10 flex flex-col min-h-[250px] overflow-hidden transition-all duration-200 hover:shadow-xl hover:border-border/20">
            <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-[14px]" style="background: linear-gradient(90deg, #8BA023, #8BA02366)"></div>
            <div class="absolute top-0 left-0 w-32 h-32 rounded-full pointer-events-none" style="background: radial-gradient(circle at top left, rgba(139,160,35,0.1), transparent 70%)"></div>
            <div class="px-6 pt-7 pb-3 flex-1">
                <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph">Total Users</p>
                <div class="flex items-end gap-3 mt-2">
                    <p class="text-[2.75rem] leading-none font-serif text-foreground">75,782</p>
                    <span class="mb-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background: rgba(139,160,35,0.17); color:#8BA023">↗ 2%</span>
                </div>
                <p class="text-[11px] font-sans mt-2.5 text-paragraph">+24.635 users from last month</p>
                <div class="flex items-center gap-4 mt-4 pt-3" style="border-top: 1px solid rgba(245,245,245,0.05)">
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph">This month</p>
                        <p class="text-sm font-serif text-foreground mt-0.5">+24.635</p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph">Last month</p>
                        <p class="text-sm font-serif text-foreground mt-0.5">51.147</p>
                    </div>
                </div>
            </div>
            <div class="w-full" style="height:100px">
                <canvas id="totalUsersChart"></canvas>
            </div>
        </div>

        {{-- Active Users --}}
        <div class="relative bg-card rounded-[14px] border border-border/10 flex flex-col min-h-[250px] overflow-hidden transition-all duration-200 hover:shadow-xl hover:border-border/20">
            <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-[14px]" style="background: linear-gradient(90deg, #8BA023, #8BA02366)"></div>
            <div class="absolute top-0 left-0 w-32 h-32 rounded-full pointer-events-none" style="background: radial-gradient(circle at top left, rgba(139,160,35,0.1), transparent 70%)"></div>
            <div class="px-6 pt-7">
                <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph">Active Users</p>
                <div class="flex items-end gap-3 mt-2">
                    <p class="text-[2.75rem] leading-none font-serif text-foreground">25,782</p>
                    <span class="mb-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold" style="background: rgba(220,50,50,0.15); color:#e05555">↘ -1%</span>
                </div>
            </div>
            <div class="flex-1 flex items-center justify-between px-6 py-3">
                {{-- Gauge SVG --}}
                <div class="relative shrink-0">
                    <svg width="148" height="148" viewBox="0 0 148 148">
                        <defs>
                            <linearGradient id="gaugeGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#8BA023" stop-opacity="0.7"/>
                                <stop offset="100%" stop-color="#8BA023"/>
                            </linearGradient>
                            <filter id="gaugeGlow" x="-20%" y="-20%" width="140%" height="140%">
                                <feGaussianBlur stdDeviation="3" result="blur"/>
                                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                            </filter>
                        </defs>
                        <circle cx="74" cy="74" r="66" fill="none" stroke="#8BA023" stroke-width="1" stroke-opacity="0.12"/>
                        <circle cx="74" cy="74" r="56" fill="none" stroke="rgba(245,245,245,0.05)" stroke-width="14" stroke-linecap="round"
                            stroke-dasharray="237.6 351.9" transform="rotate(135 74 74)"/>
                        <circle cx="74" cy="74" r="56" fill="none" stroke="#838383" stroke-width="14" stroke-linecap="round" stroke-opacity="0.2"
                            stroke-dasharray="52.3 351.9" stroke-dashoffset="-185.2" transform="rotate(135 74 74)"/>
                        <circle cx="74" cy="74" r="56" fill="none" stroke="#8BA023" stroke-width="14" stroke-linecap="round" stroke-opacity="0.2"
                            stroke-dasharray="185.2 351.9" transform="rotate(135 74 74)"/>
                        <circle cx="74" cy="74" r="56" fill="none" stroke="url(#gaugeGrad)" stroke-width="14" stroke-linecap="round"
                            filter="url(#gaugeGlow)" stroke-dasharray="185.2 351.9" transform="rotate(135 74 74)"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[1.6rem] font-serif text-foreground leading-none">78%</span>
                        <span class="text-[9px] uppercase tracking-wider text-paragraph mt-1">active rate</span>
                    </div>
                </div>
                {{-- Legend --}}
                <div class="flex flex-col gap-3 ml-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:#8BA023"></span>
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-paragraph">Active</p>
                            <p class="text-sm font-serif text-foreground">25.782</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:#838383; opacity:0.4"></span>
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-paragraph">Inactive</p>
                            <p class="text-sm font-serif text-foreground">7.259</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:#838383; opacity:0.2"></span>
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-paragraph">Total</p>
                            <p class="text-sm font-serif text-foreground">33.041</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- New Clients --}}
        <div class="relative bg-card rounded-[14px] border border-border/10 flex flex-col min-h-[250px] overflow-hidden transition-all duration-200 hover:shadow-xl hover:border-border/20">
            <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-[14px]" style="background: linear-gradient(90deg, #8BA023, #8BA02366)"></div>
            <div class="absolute top-0 left-0 w-32 h-32 rounded-full pointer-events-none" style="background: radial-gradient(circle at top left, rgba(139,160,35,0.1), transparent 70%)"></div>
            <div class="px-6 pt-7 pb-3 flex-1">
                <div class="flex items-start justify-between">
                    <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph">New Clients</p>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-[10px] text-paragraph flex items-center gap-1 border border-border/20 rounded-full px-2 py-0.5 hover:text-foreground">
                            <span x-text="clientsRange"></span> <span class="text-[8px]">▾</span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 bg-card border border-border/10 rounded-lg shadow-lg z-10 min-w-[120px]" style="display:none">
                            <template x-for="opt in rangeOptions" :key="opt">
                                <button @click="clientsRange = opt; open = false" class="block w-full text-left px-3 py-2 text-xs text-paragraph hover:text-foreground hover:bg-muted" x-text="opt"></button>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex items-end gap-3 mt-2">
                    <p class="text-[2.75rem] leading-none font-serif text-foreground" x-text="clientsData().total.toLocaleString()"></p>
                    <span class="mb-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                          :style="clientsBadgeStyle()" x-text="clientsData().change"></span>
                </div>
                <p class="text-[11px] font-sans mt-2.5 text-paragraph">Compared to <span x-text="clientsRange.toLowerCase()"></span></p>
            </div>
            <div class="w-full" style="height:100px">
                <canvas id="newClientsChart"></canvas>
            </div>
        </div>

        {{-- Active Subscriptions --}}
        <div class="relative bg-card rounded-[14px] border border-border/10 flex flex-col min-h-[250px] overflow-hidden transition-all duration-200 hover:shadow-xl hover:border-border/20">
            <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-[14px]" style="background: linear-gradient(90deg, #8BA023, #8BA02366)"></div>
            <div class="absolute top-0 left-0 w-32 h-32 rounded-full pointer-events-none" style="background: radial-gradient(circle at top left, rgba(139,160,35,0.1), transparent 70%)"></div>
            <div class="px-6 pt-7 pb-3 flex-1">
                <div class="flex items-start justify-between">
                    <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph">Active Subscriptions</p>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-[10px] text-paragraph flex items-center gap-1 border border-border/20 rounded-full px-2 py-0.5 hover:text-foreground">
                            <span x-text="subsRange"></span> <span class="text-[8px]">▾</span>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 bg-card border border-border/10 rounded-lg shadow-lg z-10 min-w-[120px]" style="display:none">
                            <template x-for="opt in rangeOptions" :key="opt">
                                <button @click="subsRange = opt; open = false" class="block w-full text-left px-3 py-2 text-xs text-paragraph hover:text-foreground hover:bg-muted" x-text="opt"></button>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="flex items-end gap-3 mt-2">
                    <p class="text-[2.75rem] leading-none font-serif text-foreground" x-text="subsData().total.toLocaleString()"></p>
                    <span class="mb-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                          :style="subsBadgeStyle()" x-text="subsData().change"></span>
                </div>
                <p class="text-[11px] font-sans mt-2.5 text-paragraph">Compared to <span x-text="subsRange.toLowerCase()"></span></p>
            </div>
            <div class="w-full" style="height:100px">
                <canvas id="activeSubsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ════════════════════════ MIDDLE CHARTS ════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Projects Growth --}}
        <div class="bg-card rounded-[14px] border border-border/10 p-6">
            <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph mb-4">Projects Growth</p>
            <div style="height:180px">
                <canvas id="projectsGrowthChart"></canvas>
            </div>
        </div>

        {{-- Plan Distribution --}}
        <div class="bg-card rounded-[14px] border border-border/10 p-6">
            <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph mb-4">Plan Distribution</p>
            <div class="flex items-center gap-6">
                <div style="height:160px; width:160px; flex-shrink:0">
                    <canvas id="planDistChart"></canvas>
                </div>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:#838383"></span>
                        <div><p class="text-[9px] uppercase tracking-widest text-paragraph">Free</p><p class="text-sm font-serif text-foreground">60%</p></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:#8BA023"></span>
                        <div><p class="text-[9px] uppercase tracking-widest text-paragraph">Smart</p><p class="text-sm font-serif text-foreground">30%</p></div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full" style="background:#d4941a"></span>
                        <div><p class="text-[9px] uppercase tracking-widest text-paragraph">Pro</p><p class="text-sm font-serif text-foreground">10%</p></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Materials --}}
        <div class="bg-card rounded-[14px] border border-border/10 p-6">
            <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph mb-4">Top Materials</p>
            <div style="height:180px">
                <canvas id="topMaterialsChart"></canvas>
            </div>
        </div>

        {{-- Cost Distribution --}}
        <div class="bg-card rounded-[14px] border border-border/10 p-6">
            <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph mb-4">Cost Distribution</p>
            <div style="height:180px">
                <canvas id="costDistChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ════════════════════════ RECENT ACTIVITY ════════════════════════ --}}
    <div class="bg-card rounded-[14px] border border-border/10 p-6">
        <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph mb-4">Recent Activity</p>
        <div class="space-y-4">
            @php
            $activities = [
                ['type'=>'project','initials'=>'AP','user'=>'Andi Pratama','action'=>'Created a new project','detail'=>'Living Room','status'=>'In progress','time'=>'2 min ago'],
                ['type'=>'plan',   'initials'=>'SD','user'=>'Sari Dewi',   'action'=>'Updated plan pricing','detail'=>'Smart Plan','status'=>'Done','time'=>'1 hour ago'],
                ['type'=>'material','initials'=>'BS','user'=>'Budi Santoso','action'=>'Added new material','detail'=>'Vinyl Flooring','status'=>'In progress','time'=>'3 hours ago'],
                ['type'=>'user',   'initials'=>'RW','user'=>'Rina Wati',   'action'=>'Registered as new user','detail'=>'Free Plan','status'=>'Done','time'=>'Yesterday'],
                ['type'=>'plan',   'initials'=>'FH','user'=>'Fajar Hidayat','action'=>'Updated feature list','detail'=>'Pro Plan','status'=>'In progress','time'=>'2 days ago'],
            ];
            $dotColors = ['project'=>'#8BA023','plan'=>'#d4941a','material'=>'#838383','user'=>'#F5F5F5'];
            @endphp
            @foreach($activities as $a)
            <div class="flex items-start gap-3">
                <div class="relative shrink-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-semibold text-foreground"
                         style="background: {{ $dotColors[$a['type']] ?? '#838383' }}">{{ $a['initials'] }}</div>
                    <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full border border-card"
                          style="background: {{ $dotColors[$a['type']] ?? '#838383' }}"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-sans text-foreground leading-tight">
                        <span class="font-medium">{{ $a['user'] }}</span>
                        <span class="text-paragraph"> {{ $a['action'] }}</span>
                    </p>
                    <p class="text-[11px] text-paragraph mt-0.5">{{ $a['detail'] }}</p>
                </div>
                <div class="text-right shrink-0">
                    <span class="px-2 py-0.5 rounded text-[10px] font-sans font-medium
                          {{ $a['status'] === 'Done' ? 'bg-status-active/15 text-status-active' : 'bg-status-warning/15 text-status-warning' }}">
                        {{ $a['status'] }}
                    </span>
                    <p class="text-[10px] text-paragraph mt-1">{{ $a['time'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardPage() {
    return {
        clientsRange: 'Last 7 days',
        subsRange: 'Last 7 days',
        rangeOptions: ['Last 7 days', 'Last 30 days', 'Last 90 days', 'This year'],
        formattedDate: new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' }),

        clientsDataMap: {
            'Last 7 days':  { spark:[10,14,8,18,12,20,15], total:6782,  change:'0% —',   dir:'flat' },
            'Last 30 days': { spark:[22,18,25,30,28,35,32,38,34,40,36,42], total:18450, change:'5% ↗',  dir:'up' },
            'Last 90 days': { spark:[40,55,48,62,58,70,65,78,72,85,80,90], total:42310, change:'12% ↗', dir:'up' },
            'This year':    { spark:[60,75,68,90,85,105,95,110,100,120,115,130], total:58920, change:'18% ↗', dir:'up' },
        },
        subsDataMap: {
            'Last 7 days':  { bars:[8,12,6,14,10,16,9],  total:2986,  change:'4% ↗',  dir:'up' },
            'Last 30 days': { bars:[12,18,10,22,15,25,14,20,16,24,13,28], total:8540, change:'7% ↗',  dir:'up' },
            'Last 90 days': { bars:[20,28,18,35,25,38,22,32,26,40,30,42], total:19870, change:'15% ↗', dir:'up' },
            'This year':    { bars:[30,42,28,50,38,55,35,48,40,58,45,62], total:34200, change:'-2% ↘', dir:'down' },
        },

        clientsData() { return this.clientsDataMap[this.clientsRange]; },
        subsData()    { return this.subsDataMap[this.subsRange]; },

        clientsBadgeStyle() {
            const d = this.clientsData();
            return d.dir==='up' ? 'background:rgba(139,160,35,0.17);color:#8BA023' :
                   d.dir==='down' ? 'background:rgba(220,50,50,0.15);color:#e05555' :
                   'background:rgba(131,131,131,0.13);color:#838383';
        },
        subsBadgeStyle() {
            const d = this.subsData();
            return d.dir==='up' ? 'background:rgba(139,160,35,0.17);color:#8BA023' :
                   d.dir==='down' ? 'background:rgba(220,50,50,0.15);color:#e05555' :
                   'background:rgba(131,131,131,0.13);color:#838383';
        },

        newClientsChart: null,
        activeSubsChart: null,

        init() {
            this.$nextTick(() => this.initCharts());
            this.$watch('clientsRange', () => this.updateClientsChart());
            this.$watch('subsRange', () => this.updateSubsChart());
        },

        chartDefaults: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false } },
            elements: { point: { radius: 0 } },
        },

        initCharts() {
            const accent = '#8BA023';
            const accentFill = 'rgba(139,160,35,0.1)';

            // Total Users spark
            new Chart(document.getElementById('totalUsersChart'), {
                type: 'line',
                data: {
                    labels: Array(20).fill(''),
                    datasets: [{ data: [20,18,25,22,30,24,35,28,22,32,26,38,30,28,34,40,36,45,42,50],
                        borderColor: accent, borderWidth: 3, tension: 0.4,
                        fill: true,
                        backgroundColor: (ctx) => {
                            const g = ctx.chart.ctx.createLinearGradient(0,0,0,100);
                            g.addColorStop(0,'rgba(139,160,35,0.5)');
                            g.addColorStop(1,'rgba(139,160,35,0)');
                            return g;
                        },
                        pointRadius: 0 }]
                },
                options: { ...this.chartDefaults }
            });

            // New Clients spark
            this.newClientsChart = new Chart(document.getElementById('newClientsChart'), {
                type: 'line',
                data: {
                    labels: Array(7).fill(''),
                    datasets: [{ data: this.clientsData().spark,
                        borderColor: accent, borderWidth: 3, tension: 0.4,
                        fill: true,
                        backgroundColor: (ctx) => {
                            const g = ctx.chart.ctx.createLinearGradient(0,0,0,100);
                            g.addColorStop(0,'rgba(139,160,35,0.5)');
                            g.addColorStop(1,'rgba(139,160,35,0)');
                            return g;
                        },
                        pointRadius: 0 }]
                },
                options: { ...this.chartDefaults }
            });

            // Active Subscriptions bar
            this.activeSubsChart = new Chart(document.getElementById('activeSubsChart'), {
                type: 'bar',
                data: {
                    labels: Array(7).fill(''),
                    datasets: [{ data: this.subsData().bars,
                        backgroundColor: accent + 'cc',
                        borderRadius: 3, barPercentage: 0.6 }]
                },
                options: { ...this.chartDefaults }
            });

            // Projects Growth line
        new Chart(document.getElementById('projectsGrowthChart'), {
                type: 'line',
                data: {
                    labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug'],
                    datasets: [{ data: [5,12,18,25,38,45,55,67],
                        borderColor: accent, borderWidth: 2.5, tension: 0.4,
                        fill: true,
                        backgroundColor: (ctx) => {
                            const g = ctx.chart.ctx.createLinearGradient(0,0,0,180);
                            g.addColorStop(0,'rgba(139,160,35,0.25)');
                            g.addColorStop(1,'rgba(139,160,35,0)');
                            return g;
                        },
                        pointRadius: 0 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color:'#838383', font:{ size:10 } }, grid:{ display:false } },
                        y: { ticks: { color:'#838383', font:{ size:10 } }, grid:{ color:'rgba(245,245,245,0.05)' } }
                    },
                    elements: { point: { radius: 0 } },
                }
            });

            // Plan Distribution doughnut
            new Chart(document.getElementById('planDistChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Free','Smart','Pro'],
                    datasets: [{ data:[60,30,10], backgroundColor:['#838383','#8BA023','#d4941a'],
                        borderWidth:0, hoverOffset:4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout:'65%',
                    plugins: { legend:{ display:false } },
                }
            });

            // Top Materials horizontal bar
            new Chart(document.getElementById('topMaterialsChart'), {
                type: 'bar',
                data: {
                    labels: ['Vinyl Flooring','Ceramic Tile','Wall Paint','Plywood','Gypsum Board'],
                    datasets: [{ data:[42,38,35,28,22],
                        backgroundColor: accent+'bb',
                        borderRadius: 4, barPercentage:0.6 }]
                },
                options: {
                    indexAxis:'y',
                    responsive:true, maintainAspectRatio:false,
                    plugins:{ legend:{ display:false } },
                    scales:{
                        x:{ ticks:{ color:'#838383', font:{size:10} }, grid:{ color:'rgba(245,245,245,0.05)' } },
                        y:{ ticks:{ color:'#838383', font:{size:10} }, grid:{ display:false } }
                    }
                }
            });

            // Cost Distribution bar
            new Chart(document.getElementById('costDistChart'), {
                type: 'bar',
                data: {
                    labels:['< 5jt','5–20jt','20–50jt','> 50jt'],
                    datasets:[{ data:[12,28,18,9],
                        backgroundColor: accent+'bb',
                        borderRadius:4, barPercentage:0.6 }]
                },
                options:{
                    responsive:true, maintainAspectRatio:false,
                    plugins:{ legend:{ display:false } },
                    scales:{
                        x:{ ticks:{ color:'#838383', font:{size:10} }, grid:{ display:false } },
                        y:{ ticks:{ color:'#838383', font:{size:10} }, grid:{ color:'rgba(245,245,245,0.05)' } }
                    }
                }
            });
        },

        updateClientsChart() {
            if (!this.newClientsChart) return;
            this.newClientsChart.data.datasets[0].data = this.clientsData().spark;
            this.newClientsChart.data.labels = Array(this.clientsData().spark.length).fill('');
            this.newClientsChart.update();
        },
        updateSubsChart() {
            if (!this.activeSubsChart) return;
            this.activeSubsChart.data.datasets[0].data = this.subsData().bars;
            this.activeSubsChart.data.labels = Array(this.subsData().bars.length).fill('');
            this.activeSubsChart.update();
        },
    }
}
</script>
@endpush

  
