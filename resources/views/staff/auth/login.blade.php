@extends('staff.layouts.master-without-nav')
@section('title')
Department Login
@endsection

@section('content')

<div class="auth-page">
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-xxl-3 col-lg-4 col-md-5">
                <div class="auth-full-page-content d-flex p-sm-5 p-4">
                    <div class="w-100">

                        <div class="auth-content my-auto">

                            <div class="text-center">
                                <h5 class="mb-0">Department Login</h5>
                                <p class="text-muted mt-2">
                                    Sign in to continue to Department Panel.
                                </p>
                            </div>

                            <form class="mt-4 pt-2"
                                  action="{{ route('staff.login.submit') }}"
                                  method="POST">
                                @csrf

                                <div class="form-floating form-floating-custom mb-4">
                                    <input type="text"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone') }}"
                                           name="phone"
                                           placeholder="Enter Phone">
                                    @error('phone')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <label>Phone</label>
                                </div>

                                <div class="form-floating form-floating-custom mb-4">
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           name="password"
                                           placeholder="Enter Password">
                                    @error('password')
                                        <span class="invalid-feedback">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <label>Password</label>
                                </div>

                                <div class="mb-3">
                                    <button class="btn btn-zopa w-100" type="submit">
                                        Log In
                                    </button>
                                </div>

                            </form>

                        </div>

                    </div>
                </div>
            </div>

            {{-- You can reuse same right side design --}}
            <div class="col-xxl-9 col-lg-8 col-md-7">
                <div class="auth-bg pt-md-5 p-4 d-flex">
                    <div class="bg-overlay"></div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
