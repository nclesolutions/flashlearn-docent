<!DOCTYPE html>
<html lang="nl">
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
                                <li class="breadcrumb-item text-white fw-bold lh-1">Studiewijzer / {{ $studyGuide->title }}</li>
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
                                        <h3 class="card-title">Studiewijzer {{ $studyGuide->title }}</h3>
                                        <div class="card-toolbar">
                                            <a href="{{ url('studiewijzer/' . $studyGuide->id . '/huiswerk/aanmaken') }}" class="btn btn-sm btn-primary">
                                                Nieuw Huiswerk
                                            </a>
                                            &nbsp;&nbsp;&nbsp;
                                            <form action="{{ route('dashboard.studyguide.verwijder', ['id' => $studyGuide->id]) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je deze studiewijzer wilt verwijderen?');">
                                                @csrf
                                                @method('DELETE')
                                                <!-- Voeg een disabled attribuut toe als er huiswerkopdrachten zijn -->
                                                <button type="submit" class="btn btn-sm btn-danger" {{ $homeworks->isNotEmpty() ? 'disabled' : '' }}>Verwijderen</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="container">
                                            <p><strong>Vak:</strong> {{ $studyGuide->subject->name }}</p>
                                            <p><strong>Klas:</strong> {{ $studyGuide->schoolClass ? $studyGuide->schoolClass->name : 'N/A' }}</p>
                                            <!-- Maak een lijst met huiswerk, filteren op homework.study_guide_id -->
                                        </div>
                                    </div>
                                </div>
                                <!-- Huiswerk kaarten -->
                                <div class="row pt-5">
                                    @if($homeworks->isEmpty())
                                    <div class="col-12">
                                        <div class="alert alert-warning" role="alert">
                                            <strong>Geen huiswerk-opdrachten gevonden.</strong> Voeg een nieuwe opdracht toe!
                                        </div>
                                    </div>
                                    @else
                                    @foreach ($homeworks as $homework)
                                    <div class="col-md-6 col-lg-4 mb-6">
                                        <div class="card card-custom rounded gutter-b">
                                            <div class="card-header pt-3 pb-2">
                                                <div class="card-title">
                                                    <i class="ki-outline ki-book-open fs-1 me-2"></i>
                                                    <h3 class="card-label">{{ $homework->title }}</h3>
                                                </div>
                                                <div class="card-toolbar">
                                                    <form action="{{ route('dashboard.studyguide.destroy_homework', ['id' => $homework->id]) }}" method="POST" onsubmit="return confirm('Weet je zeker dat je dit huiswerk wilt verwijderen?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light">Verwijderen</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="card-body py-4">
                                                <div class="d-flex align-items-center mb-4">
                                                    <div class="m-0">
                                                        <div class="d-flex flex-column flex-shrink-0 me-4">
                                                                    <span class="d-flex align-items-center fs-7 fw-bold text-gray-400 mb-1">
                                                                        <i class="ki-outline ki-book-open fs-6 text-gray-600 me-2"></i>{{ $homework->subject }}
                                                                    </span>
                                                            <span class="d-flex align-items-center fs-7 fw-bold text-gray-400 mb-1">
                                                                        <i class="ki-outline ki-calendar-2 fs-6 text-gray-600 me-2"></i>{{ \Carbon\Carbon::parse($homework->return_date)->translatedFormat('d F Y') }}
                                                                    </span>
                                                            <span class="d-flex align-items-center fs-7 fw-bold text-gray-400 mb-1">
                                                                        <i class="ki-outline ki-bookmark fs-6 text-gray-600 me-2"></i>{{ $homework->description }}
                                                                    </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
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
