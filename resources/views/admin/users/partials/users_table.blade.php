<div class="table-responsive">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>
                    <a href="javascript:void(0)" class="sortable" data-sort="name">
                        Name
                        @if(request('sort') === 'name')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="javascript:void(0)" class="sortable" data-sort="email">
                        Email
                        @if(request('sort') === 'email')
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th>Role</th>
                <th>
                    <a href="javascript:void(0)" class="sortable" data-sort="created_at">
                        Created
                        @if(request('sort') === 'created_at' || !request('sort'))
                            <i class="fas fa-sort-{{ request('direction') === 'asc' ? 'up' : 'down' }}"></i>
                        @endif
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <span class="badge" style="background-color: {{ $user->is_admin ? 'rgba(138, 43, 226, 0.7)' : 'rgba(0, 123, 255, 0.7)' }};">
                        {{ $user->is_admin ? 'Admin' : 'Customer' }}
                    </span>
                </td>
                <td>{{ $user->created_at->format('M d, Y') }}</td>
                <td class="actions-cell">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn-admin-secondary" title="View Profile">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-admin-secondary" title="Edit User">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn-admin-secondary delete-user-btn" title="Delete User">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No users found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $users->appends(request()->except('ajax'))->links() }}
</div>