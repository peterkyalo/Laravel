@extends('layouts.dashboard')

@section('title', 'Class Rehearsal & Masterclass Schedule — Baritone Music Academy')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-serif text-white fw-bold mb-0">Conservatory Class Schedule</h2>
        <span class="text-muted small">Live masterclasses, sectional rehearsals, recital dates, and office hours.</span>
    </div>

    <!-- Course Filter -->
    <div class="d-flex gap-2">
        <select id="courseFilter" class="form-select form-select-sm" style="min-width: 220px;">
            <option value="">All Masterclasses</option>
            @foreach($courses as $c)
                <option value="{{ $c->id }}">{{ $c->title }}</option>
            @endforeach
        </select>
    </div>
</div>

<!-- Calendar Card -->
<div class="card card-solid p-4">
    <div id="calendar" style="min-height: 650px;"></div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-surface text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title font-serif text-gold" id="eventTitle">Session Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Masterclass Course</small>
                    <strong class="text-white" id="eventCourse"></strong>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Scheduled Time</small>
                    <span class="text-gold" id="eventTime"></span>
                </div>
                <div class="mb-3" id="eventLocationWrap">
                    <small class="text-muted d-block">Location / Rehearsal Hall</small>
                    <span class="text-white" id="eventLocation"></span>
                </div>
                <div class="mb-3" id="eventMeetingWrap">
                    <small class="text-muted d-block">Online Video Link</small>
                    <a href="#" id="eventMeeting" target="_blank" class="btn btn-sm btn-primary mt-1">
                        <i class="bi bi-camera-video me-1"></i> Join Rehearsal Room
                    </a>
                </div>
                <div id="eventDescriptionWrap">
                    <small class="text-muted d-block">Notes & Objectives</small>
                    <p class="text-light small mb-0" id="eventDescription"></p>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-secondary text-white btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<!-- FullCalendar 6 CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/main.min.css">
<style>
/* FullCalendar dark theme styling */
.fc-theme-standard th { background: #1f2937; border-color: rgba(255,255,255,0.08); color: #cbd5e1; padding: 8px; }
.fc-theme-standard td, .fc-theme-standard .fc-scrollgrid { border-color: rgba(255,255,255,0.08); }
.fc .fc-toolbar-title { font-family: var(--font-serif); color: #fff; font-size: 1.4rem; }
.fc .fc-button-primary { background: #1f2937; border-color: rgba(255,255,255,0.15); color: #fff; }
.fc .fc-button-primary:hover { background: var(--gold); color: #000; border-color: var(--gold); }
.fc .fc-button-primary:disabled { background: #111827; }
.fc .fc-button-active { background: var(--gold) !important; color: #000 !important; border-color: var(--gold) !important; }
.fc-daygrid-day-number { color: #94a3b8; text-decoration: none; padding: 4px 8px; }
.fc-day-today { background: rgba(79, 70, 229, 0.1) !important; }
.fc-event { cursor: pointer; border-radius: 4px; padding: 2px 4px; font-size: 0.82rem; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const courseFilter = document.getElementById('courseFilter');
    const modal = new bootstrap.Modal(document.getElementById('eventModal'));

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        themeSystem: 'standard',
        events: function(info, successCallback, failureCallback) {
            let url = '{{ route("schedule.events") }}';
            if (courseFilter.value) {
                url += '?course_id=' + courseFilter.value;
            }
            fetch(url)
                .then(r => r.json())
                .then(data => successCallback(data))
                .catch(err => failureCallback(err));
        },
        eventClick: function(info) {
            const props = info.event.extendedProps;
            document.getElementById('eventTitle').innerText = props.session || info.event.title;
            document.getElementById('eventCourse').innerText = props.course || '';
            document.getElementById('eventTime').innerText = props.time || '';

            if (props.location) {
                document.getElementById('eventLocation').innerText = props.location;
                document.getElementById('eventLocationWrap').style.display = 'block';
            } else {
                document.getElementById('eventLocationWrap').style.display = 'none';
            }

            if (props.meeting_url) {
                document.getElementById('eventMeeting').href = props.meeting_url;
                document.getElementById('eventMeetingWrap').style.display = 'block';
            } else {
                document.getElementById('eventMeetingWrap').style.display = 'none';
            }

            if (props.description) {
                document.getElementById('eventDescription').innerText = props.description;
                document.getElementById('eventDescriptionWrap').style.display = 'block';
            } else {
                document.getElementById('eventDescriptionWrap').style.display = 'none';
            }

            modal.show();
        }
    });

    calendar.render();

    courseFilter.addEventListener('change', function() {
        calendar.refetchEvents();
    });
});
</script>
@endpush
@endsection
