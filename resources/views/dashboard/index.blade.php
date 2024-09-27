<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    @include('includes.meta')


    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->

    <!--begin::Vendor Stylesheets(used for this page only)-->
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/vis-timeline/vis-timeline.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Vendor Stylesheets-->

    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
</head>
<!--end::Head-->
<body id="kt_app_body" data-kt-app-header-fixed-mobile="true" data-kt-app-toolbar-enabled="true" class="app-default">
<!--begin::Theme mode setup on page load-->

<script>
    var defaultThemeMode = "light";
    var themeMode;
    if (document.documentElement) {
        if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
            themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
        } else {
            if (localStorage.getItem("data-bs-theme") !== null) {
                themeMode = localStorage.getItem("data-bs-theme");
            } else {
                themeMode = defaultThemeMode;
            }
        }
        if (themeMode === "system") {
            themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        }
        document.documentElement.setAttribute("data-bs-theme", themeMode);
    }

</script>
<!--end::Theme mode setup on page load-->
<!--begin::App-->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
        @include('includes.header')
            <!--begin::Wrapper-->
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-6">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex align-items-start">
                    <!--begin::Toolbar container-->
                    <div class="d-flex flex-column flex-row-fluid">
                        <!--begin::Toolbar wrapper-->
                        <div class="d-flex align-items-center pt-1">
                            <!--begin::Breadcrumb-->
                            <ul class="breadcrumb breadcrumb-separatorless fw-semibold">
                                <!--begin::Item-->
                                <li class="breadcrumb-item text-white fw-bold lh-1">
                                    <a href="/" class="text-white">
                                        <i class="ki-outline ki-home text-gray-700 fs-6"></i>
                                    </a>
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="breadcrumb-item">
                                    <i class="ki-outline ki-right fs-7 text-gray-700 mx-n1"></i>
                                </li>
                                <!--end::Item-->
                                <!--begin::Item-->
                                <li class="breadcrumb-item text-white fw-bold lh-1">Overzicht</li>
                                <!--end::Item-->
                            </ul>
                            <!--end::Breadcrumb-->
                        </div>
                        <!--end::Toolbar wrapper=-->
                        <!--begin::Toolbar wrapper=-->
                        <div class="d-flex flex-stack flex-wrap flex-lg-nowrap gap-4 gap-lg-10 pt-13 pb-6">
                            <!--begin::Page title-->
                            <div class="page-title me-5">
                                <!--begin::Title-->
                                <h1 class="page-heading d-flex text-white fw-bold fs-2 flex-column justify-content-center my-0">
                                    {{ begroeting() }}, {{ htmlspecialchars(Auth::user()->firstname, ENT_QUOTES) }}!
                                    <!--begin::Description-->
                                    <span class="page-desc text-gray-700 fw-semibold fs-6 pt-3">Welkom terug in de docenten-omgeving. We zijn blij je weer te zien!</span>
                                    <!--end::Description-->
                                </h1>
                                <!--end::Title-->
                            </div>
                            <!--end::Page title-->
                            <!--begin::DOMAIN-->
                            @include('includes.domain')
                                <!--end::DOMAIN-->
                        </div>
                        <!--end::Toolbar wrapper=-->
                    </div>
                    <!--end::Toolbar container=-->
                </div>
                <!--end::Toolbar container-->
            </div>
            <!--end::Toolbar-->
            <!--begin::Wrapper container-->
            <div class="app-container container-xxl">
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">
                        <!--begin::Content-->
                        <div id="kt_app_content" class="app-content flex-column-fluid">

                            @if (session('orgName'))
                            <!--begin::Row-->
                            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">

                                @if (!empty($filteredLessons))
                                <div class="col-xl-12">
                                    <div class="card rounded h-xl-100">
                                        <div class="card-header position-relative py-0 border-bottom-2">
                                            <ul class="nav nav-stretch nav-pills nav-pills-custom d-flex mt-3">
                                                @foreach ($filteredLessons as $weekNumber => $days)
                                                @foreach ($days as $dayOfWeek => $lessons)
                                                <li class="nav-item p-0 ms-0 me-8">
                                                    <a class="nav-link btn btn-color-muted px-0" data-bs-toggle="tab" href="#day-{{ $dayOfWeek }}">
                                                        <span class="nav-text fw-semibold fs-4 mb-3">{{ $dayOfWeek }}</span>
                                                        <span class="bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary rounded"></span>
                                                    </a>
                                                </li>
                                                @endforeach
                                                @endforeach
                                            </ul>
                                            <div class="d-flex align-items-center">
                                                <div id="week-info" class="text-gray-600 fw-bold me-3">
                                                    <i class="fa fa-calendar-alt me-2"></i>
                                                    <span id="week-dates">Je bekijkt momenteel het docenten-rooster.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="tab-content mb-2">
                                                @foreach ($filteredLessons as $weekNumber => $days)
                                                @foreach ($days as $dayOfWeek => $lessons)
                                                <div class="tab-pane fade" id="day-{{ $dayOfWeek }}">
                                                    <div class="table-responsive">
                                                        <table class="table align-middle">
                                                            <thead>
                                                            <tr>
                                                                <th class="min-w-150px p-0"></th>
                                                                <th class="min-w-200px p-0"></th>
                                                                <th class="min-w-100px p-0"></th>
                                                                <th class="min-w-80px p-0"></th>
                                                                <th class="min-w-80px p-0"></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @foreach ($lessons as $lesson)
                                                            <tr>
                                                                <td class="fs-6 fw-bold text-gray-800">{{ $lesson['time'] }}</td>
                                                                <td class="fs-6 fw-bold text-gray-500">
                                                                    Les: <span class="text-gray-800">{{ implode(', ', $lesson['lesson']) }}</span>
                                                                    <br>
                                                                    Klassen: <span class="text-gray-800">{{ implode(', ', $lesson['classes']) }}</span> <!-- Toon de gegroepeerde klassen -->
                                                                </td>
                                                                <td class="fs-6 fw-bold text-gray-500">Lokaal: <span class="text-gray-800">{{ $lesson['location'] }}</span></td>
                                                                <td class="fs-6 fw-bold text-gray-500">Docent: <span class="text-gray-800">{{ $lesson['teacher'] }}</span></td>
                                                                <td class="fs-6 fw-bold text-gray-500">
                                                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#studentsModal" onclick="showStudents('{{ json_encode($lesson['students']) }}')"><i class="ki-outline ki-setting fs-2"></i></button>
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                @endforeach
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Modal -->
                                <div class="modal fade" id="studentsModal" tabindex="-1" aria-labelledby="studentsModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="studentsModalLabel">Leerlingen</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <ul id="studentsList" class="list-group">
                                                    <!-- Leerlingen worden hier dynamisch toegevoegd -->
                                                </ul>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Sluiten</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    function showStudents(students) {
                                        const studentsList = document.getElementById('studentsList');
                                        studentsList.innerHTML = ''; // Maak de lijst leeg

                                        const studentNames = JSON.parse(students);
                                        studentNames.forEach(student => {
                                            const listItem = document.createElement('li');
                                            listItem.className = 'list-group-item';
                                            listItem.textContent = student;
                                            studentsList.appendChild(listItem);
                                        });
                                    }
                                </script>






                            </div>
                            @endif



                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Content wrapper-->
                    @include('includes.footer')
                </div>
                <!--end:::Main-->
            </div>
            <!--end::Wrapper container-->
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Page-->
</div>
<!--end::App-->
<!--begin::Scrolltop-->
<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
    <i class="ki-outline ki-arrow-up"></i>
</div>
<!--end::Scrolltop--><!--begin::Javascript-->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Verkrijg de huidige dag van de week (0=zondag, 6=zaterdag)
        var currentDayOfWeek = new Date().getDay();
        console.log("Huidige dag van de week (0=zondag, 6=zaterdag):", currentDayOfWeek);

        // Mapping van dagen naar de juiste dag-namen zoals ze in de tabs worden gebruikt
        var dayNameMapping = {
            0: 'Maandag',  // Zondag -> Maandag
            1: 'Maandag',
            2: 'Dinsdag',
            3: 'Woensdag',
            4: 'Donderdag',
            5: 'Vrijdag',
            6: 'Maandag'   // Zaterdag -> Maandag
        };

        // Bepaal de dag-naam gebaseerd op de huidige dag (of weekendcorrectie)
        var currentDayName = dayNameMapping[currentDayOfWeek];
        console.log("Geselecteerde dag naam:", currentDayName);

        // Zoek het juiste tab-element en content-element op basis van de dag-naam
        var currentTab = document.querySelector(`[href="#day-${currentDayName}"]`);
        var currentContent = document.getElementById(`day-${currentDayName}`);

        if (currentTab) {
            currentTab.classList.add('show', 'active');
            console.log("Tab voor " + currentDayName + " is actief gemaakt.");
        } else {
            console.log('Tab niet gevonden voor dag:', currentDayName);
        }

        if (currentContent) {
            currentContent.classList.add('show', 'active');
            console.log("Content voor " + currentDayName + " is actief gemaakt.");
        } else {
            console.log('Content niet gevonden voor dag:', currentDayName);
        }
    });


</script>



<!-- Begin met het laden van jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Laad Bootstrap-bundel, die afhankelijk is van jQuery -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Begin met het laden van de globale Javascript-bundel (vermoedelijk Metronic, die mogelijk ook afhankelijk is van jQuery en Bootstrap) -->
<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
<!-- Einde globale Javascript-bundel -->

<!-- Begin met het laden van de vendors Javascript (voor pagina-specifieke functionaliteit) -->
<script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
<script src="{{ asset('assets/plugins/custom/vis-timeline/vis-timeline.bundle.js') }}"></script>
<script src="https://cdn.amcharts.com/lib/5/index.js"></script>
<script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
<script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
<script src="https://cdn.amcharts.com/lib/5/radar.js"></script>
<script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
<!-- Einde vendors Javascript -->

<!-- Begin met het laden van de custom Javascript (voor pagina-specifieke functionaliteit en scripts die afhankelijk zijn van eerder geladen scripts) -->
<script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
<script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
<script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/new-target.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-project/type.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-project/budget.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-project/settings.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-project/team.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-project/targets.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-project/files.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-project/complete.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-project/main.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-app.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/users-search.js') }}"></script>
<!-- Einde custom Javascript -->

</body>
</html>
