@extends('layouts.base')

@section('title', 'Points of Interest - Frontier Station 14')

@section('description')
Explore points of interest from the Frontier Station server in Space Station 14. Discover unique locations, hidden areas, and special places across the station.
@endsection

@section('navigation')
    @include('layouts.poi_navigation')
@endsection

@section('content')
    <!-- Информационный блок -->
    <div id="info-block" class="info-container">
        <div class="info-block__container">
            <h1>Points of Interest in Frontier Station 14</h1>
            <p>Discover unique locations and hidden areas across the station. Click on any POI in the menu to explore!</p>
        </div>
    </div>

    <!-- Контейнер для отображения POI с параллакс-фоном -->
    <div id="poi-render-container">
        <img id="poi-render" src="" alt="POI Render">
    </div>

    <!-- Панель информации о POI -->
    <div id="poi-info-panel" class="poi-info-panel">
        <h2 id="poi-name"></h2>
    </div>

    <!-- SEO контент -->
    <div class="seo-content" style="position: absolute; left: -9999px;">
        <h2>Notable Points of Interest in Frontier Station 14</h2>
        <ul>
            @foreach($groupedPoi as $letter => $points)
                @foreach($points as $poi)
                    <li class="poi-item"
                        data-id="{{ $poi['id'] }}"
                        data-name="{{ $poi['name'] }}"
                        data-category="{{ $poi['category'] ?? '' }}"
                        data-type="{{ $poi['type'] ?? '' }}">
                        <h3 class="poi-name">{{ $poi['name'] }}</h3>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endsection

@section('footer_scripts')
<script src="{{ asset('js/poi.js?v1.0.0') }}"></script>
@endsection
