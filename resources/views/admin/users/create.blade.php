<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3 mb-4">
                            <h5 class="fw-bold border-bottom pb-2">Account Details</h5>
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" name="name" id="name" class="form-control" required value="{{ old('name') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" name="email" id="email" class="form-control" required value="{{ old('email') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" required minlength="8">
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required minlength="8">
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <h5 class="fw-bold border-bottom pb-2">Role Assignment</h5>
                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($roles as $role)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="role" id="role_{{ $role->id }}" value="{{ $role->name }}" required>
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
                                    @foreach($permissions as $permission)
                                        <div class="col-md-3 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}">
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
                            <button type="submit" class="btn btn-primary">Create User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
