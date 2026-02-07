<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3 mb-4">
                            <h5 class="fw-bold border-bottom pb-2">Account Details</h5>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control" required value="{{ old('name', $user->name) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" required value="{{ old('email', $user->email) }}">
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Password (Leave blank to keep current)</label>
                                <input type="password" name="password" id="password" class="form-control" minlength="8">
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="8">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <h5 class="fw-bold border-bottom pb-2">Role Assignment</h5>
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-3">
                                    @php $userRoles = $user->roles->pluck('name')->toArray(); @endphp
                                    @foreach($roles as $role)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="role" id="role_{{ $role->id }}" value="{{ $role->name }}" {{ in_array($role->name, $userRoles) ? 'checked' : '' }} required>
                                            <label class="form-check-label fw-bold" for="role_{{ $role->id }}">
                                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <h5 class="fw-bold border-bottom pb-2">
                                Custom Permissions 
                                <span class="text-muted fs-6 fw-normal">(Optional Override)</span>
                            </h5>
                            <div class="col-12">
                                <div class="row">
                                    @php $userPermissions = $user->getDirectPermissions()->pluck('name')->toArray(); @endphp
                                    @foreach($permissions as $permission)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}" {{ in_array($permission->name, $userPermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-text mt-2">
                                    Check these only if you want to grant extra permissions beyond the standard role.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
