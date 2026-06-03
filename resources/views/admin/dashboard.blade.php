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

    {{-- Loading state --}}
    <template x-if="loading">
        <div class="text-center py-12 text-paragraph text-sm">Loading dashboard data...</div>
    </template>

    <template x-if="!loading">
    <div class="space-y-4">

    {{-- ════════════════════════ TOP STATS ROW ════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Total Users --}}
        <div class="relative bg-card rounded-[14px] border border-border/10 flex flex-col min-h-[250px] overflow-hidden transition-all duration-200 hover:shadow-xl hover:border-border/20">
            <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-[14px]" style="background: linear-gradient(90deg, #8BA023, #8BA02366)"></div>
            <div class="absolute top-0 left-0 w-32 h-32 rounded-full pointer-events-none" style="background: radial-gradient(circle at top left, rgba(139,160,35,0.1), transparent 70%)"></div>
            <div class="px-6 pt-7 pb-3 flex-1">
                <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph">Total Users</p>
                <div class="flex items-end gap-3 mt-2">
                    <p class="text-[2.75rem] leading-none font-serif text-foreground" x-text="metrics.total_users?.toLocaleString() ?? '—'"></p>
                    <span class="mb-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold"
                          :style="metrics.users_growth >= 0 ? 'background:rgba(139,160,35,0.17);color:#8BA023' : 'background:rgba(220,50,50,0.15);color:#e05555'"
                          x-text="(metrics.users_growth >= 0 ? '↗ ' : '↘ ') + Math.abs(metrics.users_growth || 0) + '%'"></span>
                </div>
                <p class="text-[11px] font-sans mt-2.5 text-paragraph" x-text="'+' + (metrics.new_users_this_month?.toLocaleString() ?? '0') + ' users from last month'"></p>
                <div class="flex items-center gap-4 mt-4 pt-3" style="border-top: 1px solid rgba(245,245,245,0.05)">
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph">This month</p>
                        <p class="text-sm font-serif text-foreground mt-0.5" x-text="'+' + (metrics.new_users_this_month?.toLocaleString() ?? '0')"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph">Last month</p>
                        <p class="text-sm font-serif text-foreground mt-0.5" x-text="(metrics.new_users_last_month?.toLocaleString() ?? '0')"></p>
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
                    <p class="text-[2.75rem] leading-none font-serif text-foreground" x-text="metrics.online_users?.toLocaleString() ?? '—'"></p>
                </div>
            </div>
            <div class="flex-1 flex flex-col items-center justify-center gap-3 px-6 py-4">
                {{-- Gauge SVG (scalable, online/offline) --}}
                <div class="relative" style="width:120px; height:120px">
                    <svg class="w-full h-full" viewBox="0 0 148 148">
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
                        <circle cx="74" cy="74" r="56" fill="none" stroke="#8BA023" stroke-width="14" stroke-linecap="round" stroke-opacity="0.2"
                            :stroke-dasharray="gaugeOnline + ' 351.9'" transform="rotate(135 74 74)"/>
                        <circle cx="74" cy="74" r="56" fill="none" stroke="url(#gaugeGrad)" stroke-width="14" stroke-linecap="round"
                            filter="url(#gaugeGlow)" :stroke-dasharray="gaugeOnline + ' 351.9'" transform="rotate(135 74 74)"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-[1.4rem] font-serif text-foreground leading-none" x-text="metrics.online_rate + '%'"></span>
                        <span class="text-[9px] uppercase tracking-wider text-paragraph mt-1">online rate</span>
                    </div>
                </div>
                {{-- Legend (3 items horizontal) --}}
                <div class="flex items-center justify-center gap-4 flex-wrap">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full shrink-0" style="background:#8BA023"></span>
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-paragraph">Online</p>
                            <p class="text-sm font-serif text-foreground" x-text="metrics.online_users?.toLocaleString() ?? '0'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full shrink-0" style="background:#838383; opacity:0.6"></span>
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-paragraph">Offline</p>
                            <p class="text-sm font-serif text-foreground" x-text="metrics.offline_users?.toLocaleString() ?? '0'"></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full shrink-0" style="background:#838383; opacity:0.2"></span>
                        <div>
                            <p class="text-[9px] uppercase tracking-widest text-paragraph">Total</p>
                            <p class="text-sm font-serif text-foreground" x-text="metrics.total_users?.toLocaleString() ?? '0'"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Total Projects --}}
        <div class="relative bg-card rounded-[14px] border border-border/10 flex flex-col min-h-[250px] overflow-hidden transition-all duration-200 hover:shadow-xl hover:border-border/20">
            <div class="absolute top-0 left-0 right-0 h-[3px] rounded-t-[14px]" style="background: linear-gradient(90deg, #8BA023, #8BA02366)"></div>
            <div class="absolute top-0 left-0 w-32 h-32 rounded-full pointer-events-none" style="background: radial-gradient(circle at top left, rgba(139,160,35,0.1), transparent 70%)"></div>
            <div class="px-6 pt-7 pb-3 flex-1">
                <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph">Total Projects</p>
                <div class="flex items-end gap-3 mt-2">
                    <p class="text-[2.75rem] leading-none font-serif text-foreground" x-text="metrics.total_projects?.toLocaleString() ?? '—'"></p>
                </div>
                <div class="flex items-center gap-4 mt-4 pt-3" style="border-top: 1px solid rgba(245,245,245,0.05)">
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph">Draft</p>
                        <p class="text-sm font-serif text-foreground mt-0.5" x-text="metrics.projects_by_status?.draft ?? 0"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph">Estimated</p>
                        <p class="text-sm font-serif text-foreground mt-0.5" x-text="metrics.projects_by_status?.estimated ?? 0"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph">Completed</p>
                        <p class="text-sm font-serif text-foreground mt-0.5" x-text="metrics.projects_by_status?.completed ?? 0"></p>
                    </div>
                </div>
            </div>
            <div class="w-full" style="height:100px">
                <canvas id="newClientsChart"></canvas>
            </div>
        </div>

    </div>

    {{-- ════════════════════════ MIDDLE CHARTS ════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-4">

        {{-- Projects Growth --}}
        <div class="bg-card rounded-[14px] border border-border/10 p-6">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph">Projects Growth</p>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full shrink-0" style="background:#8BA023"></span>
                        <span class="text-[10px] text-paragraph uppercase tracking-widest">Users</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full shrink-0" style="background:#d4941a"></span>
                        <span class="text-[10px] text-paragraph uppercase tracking-widest">Projects</span>
                    </div>
                </div>
            </div>
            <div style="height:200px">
                <canvas id="projectsGrowthChart"></canvas>
            </div>
        </div>

        {{-- Plan Distribution --}}
        <div class="bg-card rounded-[14px] border border-border/10 p-6">
            <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph mb-4">Plan Distribution</p>
            <div class="flex flex-col items-center gap-4">
                <div style="height:150px; width:150px; flex-shrink:0">
                    <canvas id="planDistChart"></canvas>
                </div>
                <div class="flex flex-wrap justify-center gap-x-5 gap-y-2.5 w-full">
                    <template x-for="(pd, idx) in metrics.plan_distribution ?? []" :key="idx">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ background: pd.color ?? planColor(pd.name) }"></span>
                            <div>
                                <p class="text-[9px] uppercase tracking-widest text-paragraph" x-text="pd.name"></p>
                                <p class="text-sm font-serif text-foreground" x-text="pd.percentage + '%'"></p>
                                <p class="text-[9px] text-paragraph" x-text="pd.count + ' user' + (pd.count !== 1 ? 's' : '')"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    {{-- ════════════════════════ RECENT ACTIVITY ════════════════════════ --}}
    <div class="bg-card rounded-[14px] border border-border/10 p-6">
        <p class="text-[10px] uppercase tracking-[0.15em] font-sans text-paragraph mb-4">Recent Activity</p>
        <div class="space-y-4">
            <template x-if="activities.length === 0">
                <p class="text-center text-paragraph text-sm py-4">No recent activity.</p>
            </template>
            <template x-for="(a, i) in activities" :key="i">
                <div class="flex items-start gap-3">
                    <div class="relative shrink-0">
                        <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center text-[11px] font-semibold text-foreground"
                             :style="a.avatar_url ? '' : `background: ${dotColors[a.type] ?? '#838383'}`">
                            <template x-if="a.avatar_url">
                                <img :src="a.avatar_url" class="w-full h-full object-cover" alt="">
                            </template>
                            <template x-if="!a.avatar_url">
                                <span x-text="a.initials"></span>
                            </template>
                        </div>
                        <span class="absolute bottom-0 right-0 w-2 h-2 rounded-full border border-card"
                              :style="{ background: dotColors[a.type] ?? '#838383' }"></span>
                    </div>
                    <div class="flex-1 min-w-0 overflow-hidden">
                        <p class="text-sm font-sans text-foreground leading-tight">
                            <span class="font-medium" x-text="a.user"></span>
                            <span class="text-paragraph" x-text="' ' + a.action"></span>
                        </p>
                        <p class="text-[11px] text-paragraph mt-0.5 truncate" x-text="a.detail"></p>
                    </div>
                    <div class="text-right shrink-0 ml-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-sans font-medium whitespace-nowrap"
                              :class="a.status === 'Done' ? 'bg-status-active/15 text-status-active' : 'bg-status-warning/15 text-status-warning'"
                              x-text="a.status"></span>
                        <p class="text-[10px] text-paragraph mt-1 whitespace-nowrap" x-text="a.time_human"></p>
                    </div>
                </div>
            </template>
        </div>
    </div>

    </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function dashboardPage() {
    return {
        loading: true,
        metrics: {},
        activities: [],
        formattedDate: new Date().toLocaleDateString('en-US', { weekday:'long', year:'numeric', month:'long', day:'numeric' }),
        dotColors: { project:'#8BA023', plan:'#d4941a', user:'#F5F5F5', room:'#5a9fd4' },
        planColor(name) {
            const key = (name ?? '').toLowerCase().trim();
            if (key.includes('enterprise')) return '#facc15';
            if (key === 'pro' || key.includes('pro')) return '#8BA023';
            return '#838383';
        },

        get gaugeOnline() {
            const total = this.metrics.total_users ?? 0;
            const online = this.metrics.online_users ?? 0;
            return total > 0 ? parseFloat(((online / total) * 237.6).toFixed(1)) : 0;
        },

        async init() {
            try {
                const [metricsRes, activityRes] = await Promise.all([
                    fetch('/admin/dashboard/metrics').then(r => r.json()),
                    fetch('/admin/dashboard/activity').then(r => r.json()),
                ]);
                this.metrics    = metricsRes.data ?? {};
                this.activities = activityRes.data ?? [];
            } catch (e) {
                console.error('Dashboard load error:', e);
            }
            this.loading = false;
            this.$nextTick(() => this.initCharts());
        },

        initCharts() {
            const accent = '#8BA023';
            const chartData = this.metrics.chart_data ?? {};
            const usersChart = chartData.users ?? [];
            const projectsChart = chartData.projects ?? [];

            const tooltip = {
                enabled: true,
                backgroundColor: 'rgba(18,18,18,0.95)',
                titleColor: '#F5F5F5',
                bodyColor: '#a0a0a0',
                borderColor: 'rgba(139,160,35,0.25)',
                borderWidth: 1,
                padding: 10,
                cornerRadius: 8,
                displayColors: true,
                boxWidth: 8,
                boxHeight: 8,
            };

            const gradientFor = (ctx, stopTop, stopBottom) => {
                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                g.addColorStop(0, stopTop);
                g.addColorStop(1, stopBottom);
                return g;
            };

            const sparkOptions = (label) => ({
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...tooltip,
                        callbacks: { label: ctx => ` ${ctx.parsed.y} ${label}` }
                    }
                },
                scales: { x: { display: false }, y: { display: false } },
                elements: {
                    point: {
                        radius: 0,
                        hoverRadius: 5,
                        hoverBackgroundColor: accent,
                        hoverBorderColor: '#fff',
                        hoverBorderWidth: 2,
                    }
                },
                interaction: { mode: 'index', intersect: false },
            });

            // Total Users spark
            const usersCanvasEl = document.getElementById('totalUsersChart');
            if (usersCanvasEl) {
                new Chart(usersCanvasEl, {
                    type: 'line',
                    data: {
                        labels: usersChart.map(d => d.label),
                        datasets: [{
                            data: usersChart.map(d => d.count),
                            borderColor: accent, borderWidth: 3, tension: 0.4,
                            fill: true,
                            backgroundColor: ctx => gradientFor(ctx, 'rgba(139,160,35,0.5)', 'rgba(139,160,35,0)'),
                            pointRadius: 0,
                        }]
                    },
                    options: sparkOptions('users'),
                });
            }

            // Projects spark
            const newClientsEl = document.getElementById('newClientsChart');
            if (newClientsEl) {
                new Chart(newClientsEl, {
                    type: 'line',
                    data: {
                        labels: projectsChart.map(d => d.label),
                        datasets: [{
                            data: projectsChart.map(d => d.count),
                            borderColor: accent, borderWidth: 3, tension: 0.4,
                            fill: true,
                            backgroundColor: ctx => gradientFor(ctx, 'rgba(139,160,35,0.5)', 'rgba(139,160,35,0)'),
                            pointRadius: 0,
                        }]
                    },
                    options: sparkOptions('projects'),
                });
            }

            // Projects Growth — dual-line with visible nodes
            const projGrowthEl = document.getElementById('projectsGrowthChart');
            if (projGrowthEl) {
                new Chart(projGrowthEl, {
                    type: 'line',
                    data: {
                        labels: usersChart.map(d => d.label),
                        datasets: [
                            {
                                label: 'Users',
                                data: usersChart.map(d => d.count),
                                borderColor: '#8BA023', borderWidth: 2.5, tension: 0.4,
                                fill: true,
                                backgroundColor: ctx => gradientFor(ctx, 'rgba(139,160,35,0.28)', 'rgba(139,160,35,0)'),
                                pointRadius: 4, pointHoverRadius: 7,
                                pointBackgroundColor: '#8BA023',
                                pointBorderColor: '#1a1a1a', pointBorderWidth: 2,
                                pointHoverBorderColor: '#fff', pointHoverBorderWidth: 2,
                            },
                            {
                                label: 'Projects',
                                data: projectsChart.map(d => d.count),
                                borderColor: '#d4941a', borderWidth: 2.5, tension: 0.4,
                                fill: true,
                                backgroundColor: ctx => gradientFor(ctx, 'rgba(212,148,26,0.18)', 'rgba(212,148,26,0)'),
                                pointRadius: 4, pointHoverRadius: 7,
                                pointBackgroundColor: '#d4941a',
                                pointBorderColor: '#1a1a1a', pointBorderWidth: 2,
                                pointHoverBorderColor: '#fff', pointHoverBorderWidth: 2,
                            }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: { ...tooltip }
                        },
                        scales: {
                            x: { ticks: { color:'#838383', font:{ size:10 } }, grid:{ display:false } },
                            y: {
                                ticks: { color:'#838383', font:{ size:10 }, precision: 0, stepSize: 1 },
                                grid: { color:'rgba(245,245,245,0.05)' },
                                beginAtZero: true,
                            }
                        },
                    }
                });
            }

            // Plan Distribution doughnut
            const planDist = this.metrics.plan_distribution ?? [];
            const planDistEl = document.getElementById('planDistChart');
            if (planDistEl) {
                new Chart(planDistEl, {
                    type: 'doughnut',
                    data: {
                        labels: planDist.map(p => p.name),
                        datasets: [{
                            data: planDist.map(p => p.percentage),
                            backgroundColor: planDist.map(p => p.color ?? this.planColor(p.name)),
                            borderWidth: 0, hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                ...tooltip,
                                callbacks: {
                                    label: ctx => {
                                        const pd = planDist[ctx.dataIndex];
                                        return pd ? ` ${pd.name}: ${pd.count} users (${pd.percentage}%)` : '';
                                    }
                                }
                            }
                        },
                    }
                });
            }
        },
    }
}
</script>
@endpush
