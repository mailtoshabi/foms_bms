@php
    $topAllTimeTeachers = topAllTimeTeachers();

    $tabs = [
        'recent' => [
            'label' => 'Recent Performance',
            'data' => $topTeachers,
            'active' => true
        ],
        'alltime' => [
            'label' => 'All Time Performance',
            'data' => $topAllTimeTeachers,
            'active' => false
        ]
    ];
@endphp
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Top Performing Teachers</h5>
                <ul class="nav nav-pills" id="topTeachersTabs" role="tablist">
                    @foreach($tabs as $id => $tab)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $tab['active'] ? 'active' : '' }}" id="{{ $id }}-tab"
                                data-bs-toggle="tab" data-bs-target="#{{ $id }}" type="button" role="tab"
                                aria-controls="{{ $id }}" aria-selected="{{ $tab['active'] ? 'true' : 'false' }}">
                                {{ $tab['label'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="topTeachersTabsContent">
                    @foreach($tabs as $id => $tab)
                        <div class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}" id="{{ $id }}" role="tabpanel"
                            aria-labelledby="{{ $id }}-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <!-- <th>#</th> -->
                                            <th>Rank</th>
                                            <th>Name</th>
                                            <th>Classes</th>
                                            <th>Hours</th>
                                            <th>Students</th>
                                            <th>Attendance</th>
                                            <th>Score</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tab['data'] as $index => $t)
                                            @php
                                                $score = $t['score'];
                                                $tier = getTeacherRankTier($score / 4);
                                                $stars = $tier['stars'];
                                                $rankLabel = $tier['label'];
                                                $rankColor = $tier['color'];
                                            @endphp
                                            <tr>
                                                <!-- <td>
                                                                    @if($index == 0) &#x1F947;
                                                                    @elseif($index == 1) &#x1F948;
                                                                    @elseif($index == 2) &#x1F949;
                                                                    @else {{ $index + 1 }}
                                                                    @endif
                                                                </td> -->
                                                <td>
                                                    <span class="badge bg-{{ $rankColor }} me-1">{{ $rankLabel }}</span><br>
                                                    <small>
                                                        @for($s = 1; $s <= 5; $s++)
                                                            @if($s <= $stars)
                                                                <span class="text-warning">&#9733;</span>
                                                            @else
                                                                <span class="text-muted">&#9733;</span>
                                                            @endif
                                                        @endfor
                                                    </small>
                                                </td>
                                                <td>
                                                    <a href="{{ auth('admin')->check() ? route('admin.reports.teachers.show', encrypt($t['teacher']->id)) : route('staff.teachers.show', encrypt($t['teacher']->id)) }}">
                                                        {{ $t['teacher']->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $t['classes'] }}</td>
                                                <td>{{ $t['hours'] }}</td>
                                                <td>{{ $t['students'] ?? 0 }}</td>
                                                <td>{{ $t['attendance'] }}%</td>
                                                <td><span class="badge bg-success">{{ $t['score'] }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No data available.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>