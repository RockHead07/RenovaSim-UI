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
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-[11px] font-semibold text-foreground shrink-0"
                             :style="{ background: planColors[u.plan] ?? '#838383' }" x-text="initials(u.name)"></div>
                        <div>
                            <p class="text-sm font-medium text-foreground leading-tight" x-text="u.name"></p>
                            <p class="text-[11px] text-paragraph" x-text="u.email"></p>
                            <p class="text-[10px] text-paragraph mt-0.5">Role: <span class="text-foreground" x-text="u.roleLabel"></span></p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium bg-lime-400 text-black" x-text="u.status"></span>
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
                        <tr class="border-b border-border/10">
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">ID</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Name</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Email</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Role</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Plan</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Joined</th>
                            <th class="text-[10px] uppercase tracking-widest text-paragraph font-sans font-normal text-left px-5 py-3">Status</th>
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
                                <td class="px-5 py-3 text-sm font-sans text-foreground" x-text="u.name"></td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="u.email"></td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium" :class="roleBadgeClass(u.role)" x-text="u.roleLabel"></span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium" :class="planBadgeClass(u.plan)" x-text="u.plan"></span>
                                </td>
                                <td class="px-5 py-3 text-sm font-sans text-paragraph" x-text="u.joined"></td>
                                <td class="px-5 py-3">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-sans font-medium bg-primary text-primary-accent" x-text="u.status"></span>
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
        page: 1,
        perPage: 10,
        plans: ['All', 'Free', 'Smart', 'Pro'],
        planColors: { Free: '#838383', Smart: '#8BA023', Pro: '#d4941a' },
        users: [],
        async init() {
            await this.fetchUsers();
        },
        async fetchUsers() {
            try {
                const params = new URLSearchParams();
                if (this.search) params.append('search', this.search);
                if (this.planFilter !== 'All') params.append('plan', this.planFilter);
                params.append('per_page', '200');

                const res = await apiFetch(`/api/users?${params}`);
                const raw = res.data ?? [];
                this.users = raw.map(u => ({
                    id: u.id,
                    name: u.username,
                    email: u.email,
                    role: u.role ?? 'user',
                    roleLabel: { admin:'Admin', super_admin:'Super Admin', owner:'Owner' }[u.role] ?? 'User',
                    plan: u.plan_name ?? u.plan?.name ?? 'Free',
                    joined: u.created_at ? u.created_at.substring(0, 10) : '',
                    status: { inactive:'Inactive', suspended:'Suspended' }[u.account_status] ?? 'Active',
                }));
                this.page = 1;
            } catch (e) {
                console.error('Failed to fetch users:', e);
            }
        },
        async deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user?')) return;
            try {
                await apiFetch(`/api/users/${id}`, { method: 'DELETE' });
                this.users = this.users.filter(u => u.id !== id);
                this.page = 1;
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
        roleBadgeClass(role) {
            if (role === 'owner') return 'bg-amber-500/20 text-amber-400';
            if (role === 'super_admin') return 'bg-purple-500/20 text-purple-300';
            if (role === 'admin') return 'bg-primary text-primary-accent';
            return 'bg-muted text-muted-foreground';
        },
        filtered() {
            const q = this.search.toLowerCase();
            return this.users.filter(u => {
                const ms = !q || u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
                const mp = this.planFilter === 'All' || u.plan === this.planFilter;
                return ms && mp;
            });
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

  
