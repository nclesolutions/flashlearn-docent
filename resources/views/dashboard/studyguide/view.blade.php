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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.js"></script>

    <!-- Global Stylesheets Bundle -->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />

    <!-- Custom Styles -->
    <style>
        /* ... jouw bestaande stijlcode ... */
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

    // Scroll naar de huidige week bij het laden van de pagina
    function scrollToCurrentWeek() {
        var currentWeekElement = document.querySelector('.current-week');
        if (currentWeekElement) {
            currentWeekElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    document.addEventListener('DOMContentLoaded', scrollToCurrentWeek);
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
                                <li class="breadcrumb-item text-white fw-bold lh-1">Studiewijzer Bekijken</li>
                            </ul>
                        </div>
                        <div class="d-flex flex-stack flex-wrap flex-lg-nowrap gap-4 gap-lg-10 pt-13 pb-6">
                            <div class="page-title me-5">
                                <h1 class="page-heading d-flex text-white fw-bold fs-2 flex-column justify-content-center my-0">
                                    {{ $studyGuide->title }}
                                    <span class="page-desc text-gray-700 fw-semibold fs-6 pt-3">Studiewijzer voor {{ $studyGuide->subject->name }}, {{ $studyGuide->schoolClass ? $studyGuide->schoolClass->name : 'N/A' }}</span>
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
                            <div id="container">
                                <div class="card rounded">
                                    <div class="card-header">
                                        <h3 class="card-title">Studiewijzer voor {{ $studyGuide->title }}</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="container">
                                            <p><strong>Vak:</strong> {{ $studyGuide->subject->name }}</p>
                                            <p><strong>Klas:</strong> {{ $studyGuide->schoolClass ? $studyGuide->schoolClass->name : 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Huiswerk agenda -->
                                @if($homeworks->isEmpty())
                                <div class="col-12 pt-5">
                                    <div class="alert alert-info">Geen huiswerk gevonden voor deze studiewijzer.</div>
                                </div>
                                @else
                                <div id="calendar-container">
                                    <div id="kt_docs_fullcalendar_populated"></div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @include('includes.footer')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Voorbeeld van huiswerkdata als een array van objecten
    var homeworks = [
    @foreach($homeworks as $homework)
    {
        title: '{{ $homework->title }}',
            start: '{{ $homework->return_date }}',
        description: '{{ $homework->description }}'
    },
    @endforeach
    ];

    // Functie om huiswerk op te zetten in FullCalendar
    function setupCalendar(homeworks) {
        var todayDate = moment().startOf('day');
        var YM = todayDate.format('YYYY-MM');
        var calendarEl = document.getElementById('kt_docs_fullcalendar_populated');
        var calendarEvents = homeworks.map(function(homework) {
            return {
                title: homework.title,
                start: homework.start,
                description: homework.description,
                className: 'fc-event-primary' // Voeg custom classes toe zoals nodig
            };
        });

        var calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            initialView: 'dayGridMonth',
            events: calendarEvents,
            eventContent: function(info) {
                var element = info.el;
                if (info.event.extendedProps && info.event.extendedProps.description) {
                    var descriptionEl = document.createElement('div');
                    descriptionEl.innerHTML = info.event.extendedProps.description;
                    element.appendChild(descriptionEl);
                }
            }
        });

        calendar.render();
    }

    // Initialiseer de kalender met de huiswerkdata
    document.addEventListener('DOMContentLoaded', function() {
        setupCalendar(homeworks);
    });
</script>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/vis-timeline/vis-timeline.bundle.js') }}"></script>
<script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/widgets.js') }}"></script>

</body>
</html>
