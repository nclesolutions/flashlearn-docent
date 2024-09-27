<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @include('includes.meta')

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <!-- Vendor Stylesheets -->
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/vis-timeline/vis-timeline.bundle.css') }}" rel="stylesheet" type="text/css" />

    <!-- Global Stylesheets Bundle -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .points-earned {
            font-size: 24px;
            color: #4caf50;
            font-weight: bold;
            transition: font-size 0.3s ease;
        }
    </style>
</head>

<body id="kt_app_body" class="app-default">
<script>
    var defaultThemeMode = "light";
    var themeMode;
    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            themeMode = localStorage.getItem("data-bs-theme") || defaultThemeMode;
        }
        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }
</script>

<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
        @include('includes.header')
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
            <div id="kt_app_toolbar" class="app-toolbar py-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex align-items-start">
                    <div class="d-flex flex-column flex-row-fluid">
                        <div class="d-flex align-items-center pt-1">
                            <ul class="breadcrumb breadcrumb-separatorless fw-semibold">
                                <li class="breadcrumb-item text-white fw-bold lh-1">
                                    <a href="/" class="text-white">
                                        <i class="ki-outline ki-home text-gray-700 fs-6"></i>
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                                </li>
                                <li class="breadcrumb-item text-white fw-bold lh-1">Nieuwe Studiewijzer</li>
                            </ul>
                        </div>
                        <div class="d-flex flex-stack flex-wrap flex-lg-nowrap gap-4 gap-lg-10 pt-13 pb-6">
                            <div class="page-title me-5">
                                <h1 class="page-heading d-flex text-white fw-bold fs-2 flex-column justify-content-center my-0">
                                    Nieuwe Studiewijzer
                                    <span class="page-desc text-gray-700 fw-semibold fs-6 pt-3">Maak nieuwe studiewijzer aan voor je vak!</span>
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-container container-xxl">
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div class="container">
                                <div class="card rounded">
                                    <div class="card-header">
                                        <h3 class="card-title">Studiewijzers <span class="badge badge-success" style="margin-left: 5px;">BÉTA</span></h3>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('dashboard.studyguide.store') }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="subject_id" class="form-label">Vak</label>
                                                <select name="subject_id" id="subject_id" class="form-control" required>
                                                    <option value="">Selecteer een vak</option>
                                                    @foreach ($subjects as $subject)
                                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="class_id" class="form-label">Klas</label>
                                                <select name="class_id" id="class_id" class="form-control" required>
                                                    <option value="">Selecteer een klas</option>
                                                    <!-- Klassen worden hier dynamisch geladen -->
                                                </select>
                                            </div>

                                            <div class="mb-3" id="student-select-container" style="display: none;">
                                                <label for="student_ids" class="form-label">Leerlingen (optioneel)</label>
                                                <select name="student_ids[]" id="student_ids" class="form-control" multiple>
                                                    <!-- Leerlingen worden hier dynamisch geladen -->
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="name" class="form-label">Studiewijzer-naam</label>
                                                <input type="text" name="name" class="form-control" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-3">Opslaan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @include('includes.footer')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#subject_id').change(function() {
            const subjectId = $(this).val();

            if (subjectId) {
                $.ajax({
                    url: '{{ route("get.classes.by.subject") }}',
                    type: 'GET',
                    data: { subject_id: subjectId },
                    success: function(data) {
                        $('#class_id').empty().append('<option value="">Selecteer een klas</option>');
                        data.forEach(function(klass) {
                            $('#class_id').append('<option value="' + klass.id + '">' + klass.name + '</option>');
                        });
                    },
                    error: function() {
                        console.error('Er is een probleem opgetreden bij het ophalen van de gegevens.');
                    }
                });
            } else {
                $('#class_id').empty().append('<option value="">Selecteer een klas</option>');
            }
        });

        $('#class_id').change(function() {
            const classId = $(this).val();

            if (classId) {
                $.ajax({
                    url: '{{ route("get.students.by.class") }}',
                    type: 'GET',
                    data: { class_id: classId },
                    success: function(data) {
                        if (data.length > 0) {
                            $('#student-select-container').show();
                            $('#student_ids').empty();

                            data.forEach(function(student) {
                                $('#student_ids').append('<option value="' + student.student_id + '">' + student.student_name + " " + student.student_lastname + '</option>');
                            });
                        } else {
                            $('#student-select-container').hide();
                        }
                    },
                    error: function() {
                        console.error('Er is een probleem opgetreden bij het ophalen van de gegevens.');
                    }
                });
            } else {
                $('#student-select-container').hide();
                $('#student_ids').empty();
            }
        });
    });
</script>

</body>
</html>
