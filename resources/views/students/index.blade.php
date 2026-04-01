<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

<div class="container mt-4">
    <h3 class="mb-4">Student Information System</h3>

    {{-- Select2 Student Search --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">Quick Search Student</label>
        <select id="studentSearch" class="form-control" style="width: 100%">
            <option></option>
        </select>
        <small class="text-muted">Search by Student ID, Name, Course, or Email — auto-fills the form below.</small>
    </div>

    <hr class="my-4">

    {{-- Form --}}
    <form id="studentForm">
        <input type="hidden" id="recordId">

        <div class="row g-3">
            @foreach ([
                ['id' => 'student_id', 'label' => 'Student ID',      'type' => 'text'],
                ['id' => 'name',       'label' => 'Name',             'type' => 'text'],
                ['id' => 'course',     'label' => 'Course / Program', 'type' => 'text'],
                ['id' => 'email',      'label' => 'Email',            'type' => 'email'],
            ] as $field)
                <div class="col-md-6">
                    <label class="form-label">{{ $field['label'] }}</label>
                    <input type="{{ $field['type'] }}" id="{{ $field['id'] }}" class="form-control">
                </div>
            @endforeach

            <div class="col-md-6">
                <label class="form-label">Year Level</label>
                <select id="year_level" class="form-select">
                    <option value="">-- Select Year Level --</option>
                    @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', '6th Year'] as $i => $label)
                        <option value="{{ $i + 1 }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Grade</label>
                <input type="number" id="grade" class="form-control" step="0.01" min="0" max="100" placeholder="0.00 – 100.00">
            </div>
        </div>

        <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Save
            </button>
            <button type="button" onclick="resetForm()" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Clear
            </button>
        </div>
    </form>

    <hr class="my-4">

    {{-- Yajra DataTable --}}
    <table id="student-table" class="table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>ID</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Course</th>
                <th>Year Level</th>
                <th>Email</th>
                <th>Grade</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = '{{ csrf_token() }}';
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    // All route URLs in one place
    const routes = {
        data:    '{{ route("students.data") }}',
        search:  '{{ route("students.search") }}',
        store:   '{{ route("students.store") }}',
        edit:    '{{ route("students.edit",    ":id") }}',
        update:  '{{ route("students.update",  ":id") }}',
        destroy: '{{ route("students.destroy", ":id") }}',
    };

    const url = (key, id = null) => id ? routes[key].replace(':id', id) : routes[key];

    // ─── Select2 Student Search ───────────────────────────────────────────────
    $('#studentSearch').select2({
        theme:       'bootstrap-5',
        placeholder: 'Search by Student ID, Name, Course, or Email...',
        allowClear:  true,
        ajax: {
            url:      routes.search,
            dataType: 'json',
            delay:    250,
            data: params => ({
                search: params.term,
                page:   params.page || 1,
            }),
            processResults: (data, params) => ({
                results:    data.results,
                pagination: { more: data.pagination.more },
            }),
            cache: true,
        },
    });

    // When a student is selected → auto-fill the form below
    $('#studentSearch').on('select2:select', function (e) {
        const d = e.params.data;
        document.getElementById('recordId').value    = d.id;
        document.getElementById('student_id').value  = d.student_id;
        document.getElementById('name').value        = d.name;
        document.getElementById('course').value      = d.course;
        document.getElementById('year_level').value  = d.year_level;
        document.getElementById('email').value       = d.email;
        document.getElementById('grade').value       = d.grade;
    });

    // When cleared → reset the form
    $('#studentSearch').on('select2:clear', function () {
        resetForm();
    });

    // ─── Yajra DataTable ──────────────────────────────────────────────────────
    const table = $('#student-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: routes.data,
        columns: [
            { data: 'id' },
            { data: 'student_id' },
            { data: 'name' },
            { data: 'course' },
            { data: 'year_level' },
            { data: 'email' },
            { data: 'grade' },
            { data: 'action', orderable: false, searchable: false },
        ]
    });

    // ─── Form Helpers ─────────────────────────────────────────────────────────
    const getFields = () => ({
        student_id: document.getElementById('student_id').value,
        name:       document.getElementById('name').value,
        course:     document.getElementById('course').value,
        year_level: document.getElementById('year_level').value,
        email:      document.getElementById('email').value,
        grade:      document.getElementById('grade').value,
    });

    // Form submit — store or update
    document.getElementById('studentForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const id      = document.getElementById('recordId').value;
        const data    = getFields();
        const request = id
            ? axios.put(url('update', id), data)
            : axios.post(url('store'), data);

        request
            .then(res => { alert(res.data.message); resetForm(); table.ajax.reload(); })
            .catch(handleError);
    });

    // Populate form from DataTable edit button
    function editData(id) {
        axios.get(url('edit', id)).then(res => {
            const d = res.data;
            document.getElementById('recordId').value   = d.id;
            document.getElementById('student_id').value = d.student_id;
            document.getElementById('name').value       = d.name;
            document.getElementById('course').value     = d.course;
            document.getElementById('year_level').value = d.year_level;
            document.getElementById('email').value      = d.email;
            document.getElementById('grade').value      = d.grade;
        });
    }

    // Delete a record
    function deleteData(id) {
        if (!confirm('Are you sure you want to delete this student?')) return;

        axios.delete(url('destroy', id))
            .then(res => { alert(res.data.message); table.ajax.reload(); });
    }

    // Clear all inputs + reset Select2
    function resetForm() {
        ['recordId', 'student_id', 'name', 'course', 'email', 'grade']
            .forEach(id => document.getElementById(id).value = '');

        document.getElementById('year_level').value = '';

        // Reset Select2 dropdown without triggering the clear event
        $('#studentSearch').val(null).trigger('change');
    }

    // Surface Laravel validation errors
    function handleError(error) {
        if (error.response?.status === 422) {
            const messages = Object.values(error.response.data.errors)
                .map(e => e[0])
                .join('\n');
            alert(messages);
        } else {
            console.error(error);
        }
    }
</script>

</body>
</html>