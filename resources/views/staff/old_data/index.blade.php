@extends('staff.layouts.master')

@section('title', 'Old Data Import')

@section('content')

    <div class="row g-4">

        {{-- 1. Students Bulk Create Import --}}
        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm h-100 border-start border-primary border-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-primary"><i class="fas fa-users-cog me-2"></i>Bulk Create Students</h5>
                    <a href="{{ asset('assets/demo_excel/students_bulk_create_demo.csv') }}"
                        class="btn btn-sm btn-outline-primary" download>
                        <i class="fas fa-download me-1"></i> Demo
                    </a>
                </div>
                <div class="card-body d-flex flex-column">
                    @if ($errors->students_bulk_create->any())
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->students_bulk_create->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('success_students_bulk_create'))
                        <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                            {{ session('success_students_bulk_create') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error_students_bulk_create'))
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            {{ session('error_students_bulk_create') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('staff.old_data.students_bulk_create') }}" method="POST"
                        enctype="multipart/form-data" class="flex-grow-1">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Excel File</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                            <div class="form-text mt-3">
                                <p class="mb-1 fw-bold text-dark small">Required Columns:</p>
                                <ul class="ps-3 text-muted small">
                                    <li><code>admission_no</code> (e.g. ADM001)</li>
                                    <li><code>country_code</code> (Optional, e.g. +91)</li>
                                    <li><code>phone</code> (Unique, no spaces)</li>
                                    <li><code>name</code> (Student full name)</li>
                                    <li><code>whatsapp_number</code> (Only if different)</li>
                                    <li><code>starting_date</code> (Class start date)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="d-grid mt-auto">
                            <button type="submit" class="btn btn-primary"
                                onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Processing...'; this.form.submit();">
                                <i class="fas fa-file-import me-1"></i> Create Students
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 2. Classrooms Import --}}
        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm h-100 border-start border-primary border-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-primary"><i class="fas fa-chalkboard me-2"></i>Classroom Dates</h5>
                    <a href="{{ asset('assets/demo_excel/classrooms_demo.csv') }}" class="btn btn-sm btn-outline-primary"
                        download>
                        <i class="fas fa-download me-1"></i> Demo
                    </a>
                </div>
                <div class="card-body d-flex flex-column">
                    @if ($errors->classrooms->any())
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->classrooms->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('success_classrooms'))
                        <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                            {{ session('success_classrooms') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error_classrooms'))
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            {{ session('error_classrooms') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('staff.old_data.import') }}" method="POST" enctype="multipart/form-data"
                        class="flex-grow-1">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Excel File</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                            <div class="form-text mt-3">
                                <p class="mb-1 fw-bold text-dark small">Required Columns:</p>
                                <ul class="ps-3 text-muted small">
                                    <li><code>Name</code> (Classroom Name)</li>
                                    <li><code>Starting Date</code> (New start date)</li>
                                </ul>
                                <p class="small text-muted mb-0">Updates: starting_date, Created Date.</p>
                            </div>
                        </div>
                        <div class="d-grid mt-auto">
                            <button type="submit" class="btn btn-primary"
                                onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Processing...'; this.form.submit();">
                                <i class="fas fa-file-import me-1"></i> Update Classrooms
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 3. Students Import --}}
        <!-- <div class="col-xl-4 col-md-6">
                            <div class="card shadow-sm h-100 border-start border-success border-4">
                                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 text-success"><i class="fas fa-user-graduate me-2"></i>Student Data</h5>
                                    <a href="{{ asset('assets/demo_excel/students_demo.csv') }}" class="btn btn-sm btn-outline-success" download>
                                        <i class="fas fa-download me-1"></i> Demo
                                    </a>
                                </div>
                                <div class="card-body d-flex flex-column">
                                    @if ($errors->students->any())
                                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                                            <ul class="mb-0">
                                                @foreach ($errors->students->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif
                                    @if (session('success_students'))
                                        <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                                            {{ session('success_students') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif
                                    @if (session('error_students'))
                                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                                            {{ session('error_students') }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endif
                                    <form action="{{ route('staff.old_data.students_import') }}" method="POST" enctype="multipart/form-data"
                                        class="flex-grow-1">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Select Excel File</label>
                                            <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                                            <div class="form-text mt-3">
                                                <p class="mb-1 fw-bold text-dark small">Required Columns:</p>
                                                <ul class="ps-3 text-muted small">
                                                    <li><code>Country Code</code> (Optional, default +91)</li>
                                                    <li><code>Phone</code> (Student unique phone)</li>
                                                    <li><code>Admission No</code> (New ID)</li>
                                                    <li><code>Date</code> (System timestamp)</li>
                                                </ul>
                                                <p class="small text-muted mb-0">Updates: admission_no, Created Date.</p>
                                            </div>
                                        </div>
                                        <div class="d-grid mt-auto">
                                            <button type="submit" class="btn btn-success"
                                                onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Processing...'; this.form.submit();">
                                                <i class="fas fa-file-import me-1"></i> Update Students
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div> -->

        {{-- 4. Student Assignments Import --}}
        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm h-100 border-start border-info border-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-info"><i class="fas fa-link me-2"></i>Student Assignments</h5>
                    <a href="{{ asset('assets/demo_excel/student_assignments_demo.csv') }}"
                        class="btn btn-sm btn-outline-info" download>
                        <i class="fas fa-download me-1"></i> Demo
                    </a>
                </div>
                <div class="card-body d-flex flex-column">
                    @if ($errors->student_assignments->any())
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->student_assignments->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('success_student_assignments'))
                        <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                            {{ session('success_student_assignments') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error_student_assignments'))
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            {{ session('error_student_assignments') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('staff.old_data.student_assignments_import') }}" method="POST"
                        enctype="multipart/form-data" class="flex-grow-1">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Excel File</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                            <div class="form-text mt-3">
                                <p class="mb-1 fw-bold text-dark small">Required Columns:</p>
                                <ul class="ps-3 text-muted small">
                                    <li><code>Country Code</code> (Optional, default +91)</li>
                                    <li><code>Phone</code> (Student phone)</li>
                                    <li><code>Classroom Name</code> (Class Name)</li>
                                    <li><code>Date</code> (Assignment date)</li>
                                </ul>
                                <p class="small text-muted mb-0">Updates: assigned_date, Created Date in pivot
                                    table.</p>
                            </div>
                        </div>
                        <div class="d-grid mt-auto">
                            <button type="submit" class="btn btn-info text-white"
                                onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Processing...'; this.form.submit();">
                                <i class="fas fa-file-import me-1 text-white"></i> Update Assignments
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 5. Teacher Data Import --}}
        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm h-100 border-start border-warning border-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-warning"><i class="fas fa-chalkboard-teacher me-2"></i>Teacher Data</h5>
                    <a href="{{ asset('assets/demo_excel/teachers_demo.csv') }}" class="btn btn-sm btn-outline-warning"
                        download>
                        <i class="fas fa-download me-1"></i> Demo
                    </a>
                </div>
                <div class="card-body d-flex flex-column">
                    @if ($errors->teachers->any())
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->teachers->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('success_teachers'))
                        <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                            {{ session('success_teachers') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error_teachers'))
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            {{ session('error_teachers') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('staff.old_data.teachers_import') }}" method="POST" enctype="multipart/form-data"
                        class="flex-grow-1">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Excel File</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                            <div class="form-text mt-3">
                                <p class="mb-1 fw-bold text-dark small">Required Columns:</p>
                                <ul class="ps-3 text-muted small">
                                    <li><code>Country Code</code> (Optional, default +91)</li>
                                    <li><code>phone</code> (Teacher unique phone)</li>
                                    <li><code>salary_cycle_day</code> (Day of month)</li>
                                    <li><code>date</code> (System timestamp)</li>
                                </ul>
                                <p class="small text-muted mb-0">Updates: Salary Day of Month, Created Date.</p>
                            </div>
                        </div>
                        <div class="d-grid mt-auto">
                            <button type="submit" class="btn btn-warning text-dark"
                                onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Processing...'; this.form.submit();">
                                <i class="fas fa-file-import me-1"></i> Update Teachers
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 6. Teacher Assignments Import --}}
        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm h-100 border-start border-secondary border-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-secondary"><i class="fas fa-link me-2"></i>Teacher Assignments</h5>
                    <a href="{{ asset('assets/demo_excel/teacher_assignments_demo.csv') }}"
                        class="btn btn-sm btn-outline-secondary" download>
                        <i class="fas fa-download me-1"></i> Demo
                    </a>
                </div>
                <div class="card-body d-flex flex-column">
                    @if ($errors->teacher_assignments->any())
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->teacher_assignments->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('success_teacher_assignments'))
                        <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                            {{ session('success_teacher_assignments') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session('error_teacher_assignments'))
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            {{ session('error_teacher_assignments') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <form action="{{ route('staff.old_data.teacher_assignments_import') }}" method="POST"
                        enctype="multipart/form-data" class="flex-grow-1">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Excel File</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                            <div class="form-text mt-3">
                                <p class="mb-1 fw-bold text-dark small">Required Columns:</p>
                                <ul class="ps-3 text-muted small">
                                    <li><code>Country Code</code> (Optional, default +91)</li>
                                    <li><code>Phone</code> (Teacher phone)</li>
                                    <li><code>Classroom Name</code> (Class Name)</li>
                                    <li><code>Date</code> (Assignment date)</li>
                                </ul>
                                <p class="small text-muted mb-0">Updates: Assignment date, Created Date in pivot
                                    table.</p>
                            </div>
                        </div>
                        <div class="d-grid mt-auto">
                            <button type="submit" class="btn btn-secondary"
                                onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Processing...'; this.form.submit();">
                                <i class="fas fa-file-import me-1"></i> Update Assignments
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info bg-soft-info">
                <div class="card-body">
                    <h6 class="text-info"><i class="fas fa-info-circle me-1"></i> Data Migration Integrity</h6>
                    <p class="small text-muted mb-0">
                        These tools are designed for large-scale data correction. They will overwrite system timestamps
                        (created_at/updated_at)
                        to ensure your historical reports remain consistent. <strong>Always backup your data before running
                            a bulk import.</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection