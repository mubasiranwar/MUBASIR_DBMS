@extends('layouts.admin')

@section('content')

<div class="container">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3>Add Teacher</h3>
        </div>

        <div class="card-body">

            <form action="{{ route('teachers.store') }}" method="POST">

                @csrf

                <div class="row">

                    <!-- Teacher Name -->
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Teacher Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            placeholder="Enter teacher name"
                            value="{{ old('name') }}"
                            required>

                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Employee ID -->
                    <div class="col-md-6 mb-3">
                        <label for="employee_id" class="form-label">Employee ID</label>
                        <input
                            type="text"
                            id="employee_id"
                            name="employee_id"
                            class="form-control"
                            placeholder="EMP001"
                            value="{{ old('employee_id') }}"
                            required>

                        @error('employee_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="teacher@example.com"
                            value="{{ old('email') }}"
                            required>

                        @error('email')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required>

                        @error('password')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control"
                            required>
                    </div>

                    <!-- Department / Specialization -->
                    <div class="col-md-6 mb-3">
                        <label for="department" class="form-label">Subject Specialization / Department</label>
                        <input
                            type="text"
                            id="department"
                            name="department"
                            class="form-control @error('department') is-invalid @enderror"
                            placeholder="Mathematics, English, Physics"
                            value="{{ old('department') }}">

                        @error('department')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="03XXXXXXXXX"
                            value="{{ old('phone') }}"
                            required>

                        @error('phone')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                </div>

                <button type="submit" class="btn btn-success">
                    Save Teacher
                </button>

                <a href="{{ route('teachers.index') }}" class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>

    </div>

</div>

@endsection