@extends('admin.layout')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="space-y-4" x-data="usersPage()">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-3">
        {{-- Search --}}
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-paragraph" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" x-model="search" @input="page = 1; fetchUsers()" placeholder="Search by name or email…"
                   class="w-full bg-background rounded-lg pl-9 pr-4 py-2 text-sm font-sans text-foreground placeholder:text-paragraph focus:outline-none focus:border-paragraph border border-border"/>
        </div>
        {{-- Filters + Add --}}
        <div class="flex items-center gap-1.5 flex-wrap w-full sm:w-auto">
            <template x-for="p in plans" :key="p">
                <button @click="planFilter = p; page = 1; fetchUsers()"
                        :class="planFilter === p ? 'bg-primary text-primary-foreground' : 'text-paragraph hover:text-foreground hover:bg-muted'"
                        class="px-3 py-1.5 rounded-lg text-xs font-sans font-medium transition-colors duration-200"
                        x-text="p"></button>
            </template>
            
            <!-- Role Filter Select -->
            <select x-model="roleFilter" @change="page = 1"
                    class="bg-[#1b1c1e] text-foreground border border-border/10 rounded-lg px-2.5 py-1.5 text-xs font-sans focus:outline-none focus:border-[#8BA023] transition-colors cursor-pointer select-none">
                <option value="All">All Roles</option>
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="super_admin">Super Admin</option>
                <option value="owner">Owner</option>
            </select>

            <!-- Status Filter Select -->
            <select x-model="statusFilter" @change="page = 1"
                    class="bg-[#1b1c1e] text-foreground border border-border/10 rounded-lg px-2.5 py-1.5 text-xs font-sans focus:outline-none focus:border-[#8BA023] transition-colors cursor-pointer select-none">
                <option value="All">All Status</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>

            <a href="/admin/users/create"
               class="ml-auto flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add User
            </a>
        </div>
    </div>

    {{-- Mobile: card list --}}
    <div class="flex flex-col gap-3 sm:hidden">
        <template x-if="paginated().length === 0">
            <p class="text-center text-paragraph text-sm py-8">No users found.</p>
        </template>
        <template x-for="u in paginated()" :key="u.id">
            <div class="bg-card rounded-[12px] border border-border/10 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full overflow-hidden flex items-center justify-center text-[11px] font-semibold text-foreground shrink-0"
                             :style="u.avatar_url ? '' : `background: ${planColors[u.plan] ?? '#838383'}`">
                            <template x-if="u.avatar_url">
                                <img :src="u.avatar_url" class="w-full h-full object-cover" alt="">
                            </template>
                            <template x-if="!u.avatar_url">
                                <span x-text="initials(u.name)"></span>
                            </template>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-foreground leading-tight" x-text="u.name"></p>
                            <p class="text-[11px] text-paragraph" x-text="u.email"></p>
                            <p class="text-[10px] text-paragraph mt-0.5">Role: <span class="text-foreground" x-text="u.roleLabel"></span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <template x-if="u.is_online">
                            <span class="flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span></span>
                        </template>
                        <template x-if="!u.is_online">
                            <span class="inline-flex rounded-full h-2 w-2 bg-muted-foreground/30"></span>
                        </template>
                        <span x-text="u.is_online ? 'Online' : 'Offline'"
                              :class="u.is_online ? 'text-green-500' : 'text-paragraph'"
                              class="text-xs font-medium"></span>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-border/10 text-center">
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">ID</p>
                        <p class="text-xs font-medium text-foreground" x-text="'#' + u.id"></p>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Plan</p>
                        <span class="px-2 py-0.5 rounded text-xs font-medium" :class="planBadgeClass(u.plan)" x-text="u.plan"></span>
                    </div>
                    <div>
                        <p class="text-[9px] uppercase tracking-widest text-paragraph mb-0.5">Joined</p>
                        <p class="text-xs font-medium text-foreground" x-text="u.joined"></p>
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <a :href="`/admin/users/${u.id}/edit`" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium bg-foreground text-background text-center">Edit</a>
                    <button @click="deleteUser(u.id)" class="flex-1 py-1.5 rounded-lg text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
                </div>
            </div>
        </template>
        {{-- Pagination --}}
        <div class="flex justify-center gap-2" x-show="totalPages() > 1">
            <template x-for="p in totalPages()" :key="p">
                <button @click="page = p"
                        :class="p === page ? 'bg-lime-400 text-black' : 'text-paragraph hover:text-foreground hover:bg-muted'"
                        class="w-8 h-8 rounded text-xs font-sans font-medium transition-colors duration-200"
                        x-text="p"></button>
            </template>
        </div>
    </div>

    {{-- Desktop: full table --}}
    <div class="hidden sm:block">
        <div class="bg-card rounded-[10px] overflow-hidden border border-border/10">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border/10 select-none">
                            <th @click="toggleSort('id')" class="cursor-pointer hover:text-foreground text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 transition-colors">
                                ID <span x-show="sortBy === 'id'" x-text="sortDesc ? '▼' : '▲'" class="text-[8px] ml-0.5"></span>
                            </th>
                            <th @click="toggleSort('name')" class="cursor-pointer hover:text-foreground text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 transition-colors">
                                Name <span x-show="sortBy === 'name'" x-text="sortDesc ? '▼' : '▲'" class="text-[8px] ml-0.5"></span>
                            </th>
                            <th @click="toggleSort('email')" class="cursor-pointer hover:text-foreground text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 transition-colors">
                                Email <span x-show="sortBy === 'email'" x-text="sortDesc ? '▼' : '▲'" class="text-[8px] ml-0.5"></span>
                            </th>
                            <th @click="toggleSort('role')" class="cursor-pointer hover:text-foreground text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 transition-colors">
                                Role <span x-show="sortBy === 'role'" x-text="sortDesc ? '▼' : '▲'" class="text-[8px] ml-0.5"></span>
                            </th>
                            <th @click="toggleSort('plan')" class="cursor-pointer hover:text-foreground text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 transition-colors">
                                Plan <span x-show="sortBy === 'plan'" x-text="sortDesc ? '▼' : '▲'" class="text-[8px] ml-0.5"></span>
                            </th>
                            <th @click="toggleSort('joined')" class="cursor-pointer hover:text-foreground text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 transition-colors">
                                Joined <span x-show="sortBy === 'joined'" x-text="sortDesc ? '▼' : '▲'" class="text-[8px] ml-0.5"></span>
                            </th>
                            <th @click="toggleSort('is_online')" class="cursor-pointer hover:text-foreground text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3 transition-colors" title="Online = active in last 10 minutes">
                                Status <span x-show="sortBy === 'is_online'" x-text="sortDesc ? '▼' : '▲'" class="text-[8px] ml-0.5"></span>
                            </th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="paginated().length === 0">
                            <tr><td colspan="8" class="text-center text-paragraph text-sm py-8">No users found.</td></tr>
                        </template>
                        <template x-for="(u, i) in paginated()" :key="u.id">
                            <tr class="hover:bg-muted/50 transition-colors duration-200 border-b border-border/5">
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="'#' + u.id"></td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full overflow-hidden flex items-center justify-center text-xs font-semibold text-foreground shrink-0"
                                             :style="u.avatar_url ? '' : `background: ${planColors[u.plan] ?? '#838383'}`">
                                            <template x-if="u.avatar_url">
                                                <img :src="u.avatar_url" class="w-full h-full object-cover" alt="">
                                            </template>
                                            <template x-if="!u.avatar_url">
                                                <span x-text="initials(u.name)"></span>
                                            </template>
                                        </div>
                                        <span class="text-sm font-sans text-foreground" x-text="u.name"></span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="u.email"></td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium" :class="roleBadgeClass(u.role)" x-text="u.roleLabel"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium" :class="planBadgeClass(u.plan)" x-text="u.plan"></span>
                                </td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="u.joined"></td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="relative shrink-0">
                                            <template x-if="u.is_online">
                                                <span class="flex h-2.5 w-2.5">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                                                </span>
                                            </template>
                                            <template x-if="!u.is_online">
                                                <span class="inline-flex rounded-full h-2.5 w-2.5 bg-muted-foreground/30"></span>
                                            </template>
                                        </div>
                                        <span x-text="u.is_online ? 'Online' : 'Offline'"
                                              :class="u.is_online ? 'text-green-500' : 'text-paragraph'"
                                              class="text-xs font-medium"></span>
                                    </div>
                                    <p class="text-[10px] text-paragraph mt-0.5 pl-0.5" x-text="u.last_active"></p>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex gap-2">
                                        <a :href="`/admin/users/${u.id}/edit`" class="px-3 py-1 rounded text-xs font-sans font-medium bg-foreground text-background">Edit</a>
                                        <button @click="deleteUser(u.id)" class="px-3 py-1 rounded text-xs font-sans font-medium" style="background:rgba(220,50,50,0.15);color:hsl(var(--destructive))">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex justify-center gap-2 mt-3" x-show="totalPages() > 1">
            <template x-for="p in totalPages()" :key="p">
                <button @click="page = p"
                        :class="p === page ? 'bg-lime-400 text-black' : 'text-paragraph hover:text-foreground hover:bg-muted'"
                        class="w-8 h-8 rounded text-xs font-sans font-medium transition-colors duration-200"
                        x-text="p"></button>
            </template>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function usersPage() {
    return {
        search: '',
        planFilter: 'All',
        roleFilter: 'All',
        statusFilter: 'All',
        sortBy: 'id',
        sortDesc: false,
        page: 1,
        perPage: 10,
        plans: ['All', @foreach(\App\Models\PricingPlan::where('is_active', true)->get() as $p)'{{ $p->name }}', @endforeach],
        planColors: { Free: '#838383', Smart: '#8BA023', Pro: '#d4941a' },
        users: [],
        async init() {
            await this.fetchUsers();
            setInterval(() => this.fetchUsers(), 60000);
        },
        async fetchUsers() {
            try {
                const params = new URLSearchParams();
                if (this.search) params.append('search', this.search);
                if (this.planFilter !== 'All') params.append('plan', this.planFilter);

                const res = await fetch(`/admin/users-api?${params}`);
                const raw = await res.json();

                this.users = raw.map(u => ({
                    id:            u.id,
                    name:          u.name,
                    email:         u.email,
                    role:          u.role,
                    avatar_url:    u.avatar_url ?? null,
                    roleLabel:     u.roleLabel,
                    plan:          u.plan,
                    joined:        u.joined,
                    status:        u.status,
                    is_online:     u.is_online ?? false,
                    online_status: u.online_status ?? 'offline',
                    last_active:   u.last_active ?? 'Never',
                }));
                this.page = 1;
            } catch (e) {
                console.error('Failed to fetch users:', e);
            }
        },
        async deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user?')) return;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(`/admin/users/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                });
                if (res.ok) {
                    this.users = this.users.filter(u => u.id !== id);
                    this.page = 1;
                } else {
                    alert('Error deleting user');
                }
            } catch (e) {
                alert('Error deleting user');
            }
        },
        initials(name) {
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0,2);
        },
        planBadgeClass(plan) {
            return { Free:'bg-muted text-muted-foreground', Smart:'bg-status-active/15 text-status-active', Pro:'bg-status-warning/15 text-status-warning' }[plan] ?? 'bg-muted text-muted-foreground';
        },
        statusBadgeClass(status) {
            return { Active:'bg-primary text-primary-accent', Inactive:'status-badge-inactive', Suspended:'bg-status-warning/15 text-status-warning' }[status] ?? 'bg-muted text-muted-foreground';
        },
        roleBadgeClass(role) {
            if (role === 'owner') return 'bg-amber-500/20 text-amber-400';
            if (role === 'super_admin') return 'bg-purple-500/20 text-purple-300';
            if (role === 'admin') return 'bg-primary text-primary-accent';
            return 'bg-muted text-muted-foreground';
        },
        toggleSort(field) {
            if (this.sortBy === field) {
                this.sortDesc = !this.sortDesc;
            } else {
                this.sortBy = field;
                this.sortDesc = false;
            }
            this.page = 1;
        },
        filtered() {
            const q = this.search.toLowerCase();
            let result = this.users.filter(u => {
                const ms = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
                const mp = this.planFilter === 'All' || u.plan === this.planFilter;
                const mr = this.roleFilter === 'All' || u.role === this.roleFilter;
                const mo = this.statusFilter === 'All' || 
                           (this.statusFilter === 'online' && u.is_online) || 
                           (this.statusFilter === 'offline' && !u.is_online);
                return ms && mp && mr && mo;
            });

            const field = this.sortBy;
            const desc = this.sortDesc;
            result.sort((a, b) => {
                let valA = a[field];
                let valB = b[field];

                if (typeof valA === 'string') {
                    valA = valA.toLowerCase();
                    valB = valB.toLowerCase();
                }

                if (valA < valB) return desc ? 1 : -1;
                if (valA > valB) return desc ? -1 : 1;
                return 0;
            });

            return result;
        },
        totalPages() { return Math.max(1, Math.ceil(this.filtered().length / this.perPage)); },
        paginated() {
            const f = this.filtered();
            return f.slice((this.page - 1) * this.perPage, this.page * this.perPage);
        },
    }
}
</script>
@endpush

  
