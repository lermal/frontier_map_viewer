@extends('layouts.base')

@section('title', isset($_GET['shuttle']) ? $_GET['shuttle'] . ' - Shipyard' : 'Frontier Shipyard')

@section('description')
{{ isset($_GET['shuttle']) ? 'View the ' . $_GET['shuttle'] . ' shuttle design and specifications in Space Station 14.' : 'Explore and view shuttles from the Frontier Station server in Space Station 14. Discover unique shuttle designs, stats, and features in an immersive online experience.' }}
@endsection

@section('head_scripts')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Frontier Shipyard",
  "description": "Space Station 14 shuttle viewer for Frontier Station server",
  "url": "https://shipyard.frontierstation14.com",
  "hasPart": [
    @foreach($groupedShuttles as $group => $categories)
        @foreach($categories['default'] as $shuttle)
        {
          "@type": "WebPage",
          "name": "{{ $shuttle['name'] }} Shuttle",
          "description": "View the {{ $shuttle['name'] }} shuttle design and specifications",
          "url": "https://shipyard.frontierstation14.com/?shuttle={{ $shuttle['id'] }}"
        }@if(!$loop->parent->last || !$loop->last),@endif
        @endforeach
    @endforeach
  ]
}
</script>
@endsection

@section('content')
    <!-- Информационный блок -->
    <div id="info-block" class="info-container">
        <div class="info-block__container">
            <h1>Welcome to the Shipyard</h1>
            <p>To get started, select a ship in the top left menu!</p>
        </div>
    </div>

    <!-- Контейнер для отображения шаттла с параллакс-фоном -->
    <div id="shuttle-render-container">
        <img id="shuttle-render" src="" alt="Shuttle Render">
    </div>

    <!-- Панель информации о шаттле -->
    <div id="shuttle-info-panel" class="shuttle-info-panel">
        <h2 id="shuttle-name"></h2>
        <div id="shuttle-price" class="shuttle-price"></div>
        <p id="shuttle-description"></p>
        <div id="shuttle-class" class="shuttle-class-tags"></div>
    </div>

    <!-- SEO контент -->
    <div class="seo-content" style="position: absolute; left: -9999px;">
        <h2>Available Shuttles in Frontier Shipyard</h2>
        <ul>
            @foreach($groupedShuttles as $group => $categories)
                @foreach($categories['default'] as $shuttle)
                    <li class="shuttle-item"
                        data-id="{{ $shuttle['id'] }}"
                        data-name="{{ $shuttle['name'] }}"
                        data-price="{{ $shuttle['price'] }}"
                        data-category="{{ $shuttle['category'] }}"
                        data-description="{{ $shuttle['description'] }}"
                        data-class="{{ implode(', ', $shuttle['class']) }}"
                        data-engines="{{ implode(', ', $shuttle['engine']) }}">
                        <h3 class="shuttle-name">{{ $shuttle['name'] }}</h3>
                        <p class="shuttle-description">{{ $shuttle['description'] }}</p>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>
@endsection

@section('footer_scripts')
<script src="{{ asset('js/main.js?v3.0.1') }}"></script>
@endsection
