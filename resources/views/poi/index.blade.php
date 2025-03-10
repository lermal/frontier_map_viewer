@extends('layouts.base')

@section('title', 'Points of Interest - Frontier Station 14')

@section('description')
Explore points of interest from the Frontier Station server in Space Station 14. Discover unique locations, hidden areas, and special places across the station.
@endsection

@section('navigation')
    @include('layouts.poi_navigation')
@endsection

@section('content')
<div class="container">
    <div id="info-block" class="info-container">
        <div class="info-block__container">
            <h1>Points of Interest in Frontier Station 14</h1>
            <p>Discover unique locations and hidden areas across the station. Click on any POI in the menu to explore!</p>
        </div>
    </div>

    <!-- Контейнер для отображения POI -->
    <div id="poi-render-container">
    </div>

    <!-- Панель информации о POI -->
    <div id="poi-info-panel" class="poi-info-panel">
        <h2 id="poi-name"></h2>
    </div>

    <!-- SEO контент -->
    <div class="seo-content" style="position: absolute; left: -9999px;">
        <h2>Notable Points of Interest in Frontier Station 14</h2>
        <ul>
            @foreach($pois as $poi)
                <li class="poi-item"
                    data-id="{{ $poi['id'] }}"
                    data-name="{{ $poi['name'] }}">
                    <h3 class="poi-name">{{ $poi['name'] }}</h3>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection

@section('head_styles')
<style>
.container {
    max-width: 100%;
    height: 100vh;
    position: relative;
    overflow: hidden;
}

.info-container {
    padding: 20px;
    margin: 20px;
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    );
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 2;
}

.info-block__container {
    text-align: center;
}

.info-block__container h1 {
    color: #a29778;
    font-size: 24px;
    margin-bottom: 10px;
}

.info-block__container p {
    color: #f2f4f1;
    font-size: 16px;
}

#poi-render-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1;
    cursor: grab;
    overflow: hidden;
}

#poi-render-container:active {
    cursor: grabbing;
}

#poi-render {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transform-origin: center;
    transition: transform 0.1s ease-out;
    user-select: none;
    -webkit-user-drag: none;
}

.poi-info-panel {
    position: fixed;
    bottom: 20px;
    left: 20px;
    background-color: rgba(36, 36, 36, 0.8);
    padding: 20px;
    z-index: 3;
    min-width: 300px;
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    );
    display: none;
}

.poi-info-panel h2 {
    color: #a29778;
    font-size: 20px;
    margin: 0;
}

@media (max-width: 768px) {
    .info-container {
        margin: 10px;
        padding: 15px;
    }

    .poi-info-panel {
        left: 10px;
        bottom: 10px;
        min-width: calc(100% - 20px);
    }
}
</style>
@endsection
