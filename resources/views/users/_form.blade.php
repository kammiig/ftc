@csrf
@isset($method)
    @method($method)
@endisset

<div class="card" style="max-width: 760px">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" @if(! $user->exists) required @endif>
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm password</label>
                <input type="password" class="form-control" name="password_confirmation" @if(! $user->exists) required @endif>
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select class="form-select" name="role" required>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                    <option value="staff" @selected(old('role', $user->role) === 'staff')>Staff</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select class="form-select" name="status" required>
                    <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                    <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="col-12">
                <button class="btn btn-primary"><i data-lucide="save"></i> Save</button>
            </div>
        </div>
    </div>
</div>
