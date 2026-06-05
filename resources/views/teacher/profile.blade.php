@extends('teacher.layouts.master-layouts-noleft')

@section('title', 'My Profile')

@section('content')

    @include('components.alerts')

    <div class="row">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body py-4">
                    <div class="position-relative d-inline-block mx-auto mb-3" style="width: 120px; height: 120px;">
                        <img class="rounded-circle"
                            src="@if($teacher->photo){{ asset('storage/' . $teacher->photo) }}@else https://ui-avatars.com/api/?name={{ urlencode($teacher->name) }}&size=150 @endif"
                            alt="Profile" width="120" height="120" style="object-fit: cover; border: 3px solid #f1f5f9;">
                        <button class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center"
                                style="width: 32px; height: 32px; border: 2px solid white;"
                                data-bs-toggle="modal" data-bs-target="#changePhotoModal"
                                title="Change profile picture">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h5>{{ $teacher->name }}</h5>
                    <p class="text-muted mb-1">{{ $teacher->admission_no }}</p>
                    <span
                        class="badge bg-{{ $teacher->status == 'active' ? 'success' : 'danger' }}">{{ ucfirst($teacher->status) }}</span>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th width="200">Name</th>
                                    <td>{{ $teacher->name }}</td>
                                </tr>
                                <tr>
                                    <th>Date of Birth</th>
                                    <td>{{ $teacher->dob ? $teacher->dob->format('d M Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $teacher->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Contact Number</th>
                                    <td>{{ $teacher->formatted_contact_number }}
                                        <br><small class="text-success"><i class="mdi mdi-whatsapp"></i>
                                            {{ $teacher->formatted_whatsapp_number }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Qualification</th>
                                    <td>{{ $teacher->qualification ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Experience</th>
                                    <td>{{ $teacher->experience ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ $teacher->address ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Assigned Classes</h5>
                </div>
                <div class="card-body">
                    @if($classes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Class</th>
                                        <th>Course</th>
                                        <th>Type</th>
                                        <th>Hourly Wage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($classes as $class)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $class->name }}</td>
                                            <td>{{ $class->course->name ?? '-' }}</td>
                                            <td><span
                                                    class="badge bg-soft-primary text-primary">{{ ucwords($class->classType->name ?? '-') }}</span>
                                            </td>
                                            <td>{{ $class->pivot->hourly_wage ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No classes assigned.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Change Photo Modal --}}
    <div class="modal fade" id="changePhotoModal" tabindex="-1" aria-labelledby="changePhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                <form action="{{ route('teacher.profile.update-photo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold" id="changePhotoModalLabel">Update Profile Picture</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <div class="p-4 bg-light rounded-3 border border-dashed text-center position-relative" style="border-style: dashed !important; border-width: 2px !important; border-color: #cbd5e1 !important;">
                                <i class="fas fa-cloud-upload-alt text-primary fs-2 mb-2"></i>
                                <p class="mb-1 text-dark small fw-semibold">Choose an image to upload</p>
                                <span class="text-muted small d-block mb-3" style="font-size: 0.75rem;">Supported formats: JPG, PNG, JPEG. Max size: 2MB</span>
                                <input type="file" name="photo" class="form-control" accept="image/*" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Upload Image</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection