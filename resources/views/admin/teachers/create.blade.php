@extends('layouts.admin')

@section('content')

<div class="container">

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3>Add Teacher</h3>
        </div>

        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('teachers.store') }}" method="POST">

                @csrf

                {{-- ── Section 1: Personal Info ── --}}
                <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">👤 Personal Information</h6>
                <div class="row">

                    {{-- Teacher Name --}}
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label fw-semibold">Teacher Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="e.g. Maam Sumayyea"
                            value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Employee ID --}}
                    <div class="col-md-6 mb-3">
                        <label for="employee_id" class="form-label fw-semibold">Employee ID <span class="text-danger">*</span></label>
                        <input type="text" id="employee_id" name="employee_id"
                            class="form-control @error('employee_id') is-invalid @enderror"
                            placeholder="e.g. EMP001"
                            value="{{ old('employee_id') }}" required>
                        <div class="form-text text-muted">⚠️ Enter a unique code like EMP001, T-001 — NOT an email address</div>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="e.g. teacher@school.com"
                            value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label fw-semibold">Phone Number</label>
                        <input type="text" id="phone" name="phone"
                            class="form-control @error('phone') is-invalid @enderror"
                            placeholder="e.g. 03349256238"
                            value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div class="col-md-12 mb-3">
                        <label for="department" class="form-label fw-semibold">Subject Specialization / Department</label>
                        <input type="text" id="department" name="department"
                            class="form-control @error('department') is-invalid @enderror"
                            placeholder="e.g. Mathematics, Physics, DBMS"
                            value="{{ old('department') }}">
                        @error('department')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- ── Section 2: Login Password ── --}}
                <h6 class="text-muted fw-bold mb-3 border-bottom pb-2 mt-2">🔒 Login Password</h6>
                <div class="row">

                    {{-- Password --}}
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Minimum 8 characters" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control"
                            placeholder="Re-enter the same password" required>
                        <div class="form-text text-muted">⚠️ Must exactly match the password above</div>
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="mt-3">
                    <button type="submit" class="btn btn-success px-4">
                        ✅ Save Teacher
                    </button>
                    <a href="{{ route('teachers.index') }}" class="btn btn-secondary ms-2">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection