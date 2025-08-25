<script src="{{ asset('js/sweetalert2@11.js') }}"></script>

<nav class="navigation">
    <nav class="navbar">
        <div class="navbar-brand">
            <img src="{{ asset('images/Logo.webp') }}" alt="Frontier Station" width="140" height="62.06">
        </div>
        <div class="navbar-items">
            <a class="btn-primary" href="/poi"><i class="ri-map-pin-2-fill"></i> Render's of POI</a>
            <a class="btn-primary" href="https://discord.gg/frontier"><i class="ri-discord-fill"></i> Discord</a>
            <a class="btn-primary" href="https://frontierstation14.com/"><i class="ri-booklet-fill"></i> Wiki</a>
            <a class="btn-primary" href="https://github.com/new-frontiers-14/frontier-station-14"><i class="ri-github-fill"></i> Github</a>
        </div>
    </nav>
    <div class="dropdown">
        <div class="dropbtn__container">
            <button class="dropbtn btn-primary shuttle-toggle"><i class="ri-corner-left-down-line"></i> Select ship</button>
            <button class="dropbtn btn-primary filter-toggle"><i class="ri-filter-3-line"></i> Filter ships</button>
        </div>
        <div class="filter-dropdown">
            <div class="filter-container">
                <div class="filter-section">
                    <h3 class="filter-title">Categories</h3>
                    <div class="checkbox-list">
                        @foreach($uniqueCategories as $category)
                            <label class="checkbox-item">
                                <input type="checkbox" value="{{ $category }}" class="shuttle-filter" data-filter-type="category">
                                <span class="checkbox-text">{{ $category }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="filter-section">
                    <h3 class="filter-title">Classes</h3>
                    <div class="checkbox-list">
                        @foreach($uniqueClasses as $class)
                            <label class="checkbox-item">
                                <input type="checkbox" value="{{ $class }}" class="shuttle-filter" data-filter-type="class">
                                <span class="checkbox-text">{{ $class }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="filter-section">
                    <h3 class="filter-title">Engines</h3>
                    <div class="checkbox-list">
                        @foreach($uniqueEngines as $engine)
                            <label class="checkbox-item">
                                <input type="checkbox" value="{{ $engine }}" class="shuttle-filter" data-filter-type="engine">
                                <span class="checkbox-text">{{ $engine }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="dropdown-content">
            <div id="no-results-message" style="display: none; text-align: center; padding: 20px; color: #f2f4f1;">
                No ships found matching the selected filters
            </div>
            @foreach($groupedShuttles as $group => $categories)
                <div class="dropdown-category">
                    <strong class="group-name">
                        @switch($group)
                            @case('BlackMarket')
                                Pirate Fleet
                                @break
                            @case('Security')
                                NSFD Fleet
                                @break
                            @case('Scrap')
                                Scrapyard Fleet
                                @break
                            @case('Sr')
                                Frontier Outpost Vessel
                                @break
                            @case('Expedition')
                                Expedition Vessels
                                @break
                            @case('Shipyard')
                                Civilian Vessels
                                @break
                            @case('Custom')
                                NSFD Mothership Fleet
                                @break
                            @case('McCargo')
                                McCargo Mothership Fleet
                                @break
                            @default
                                {{ $group }}
                        @endswitch
                    </strong>
                    <hr>
                    <ul class="shuttle-list">
                        @foreach($categories['default'] as $shuttle)
                            <li class="shuttle-item"
                                data-id="{{ $shuttle['id'] }}"
                                data-name="{{ $shuttle['name'] }}"
                                data-price="{{ $shuttle['price'] }}"
                                data-category="{{ $shuttle['category'] }}"
                                data-description="{{ $shuttle['description'] }}"
                                data-class="{{ implode(', ', $shuttle['class']) }}"
                                data-engines="{{ implode(', ', $shuttle['engine']) }}">
                                <div class="shuttle-name">{{ $shuttle['name'] }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</nav>

<style>
.filter-container {
    background-color: rgba(36, 36, 36, 0.8);
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    );
}

.checkbox-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
}

.checkbox-item {
    display: flex;
    align-items: center;
    cursor: pointer;
    color: #f2f4f1;
    padding: 5px;
    transition: background-color 0.2s;
}

.checkbox-item:hover {
    background-color: #464b45;
}

.checkbox-item input[type="checkbox"] {
    display: none;
}

.checkbox-text {
    position: relative;
    padding-left: 25px;
}

.checkbox-text:before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    background-color: #464b45;
    clip-path: polygon(
        0 0,
        calc(100% - 4px) 0,
        100% 4px,
        100% 100%,
        4px 100%,
        0 calc(100% - 4px)
    );
}

.checkbox-item input[type="checkbox"]:checked + .checkbox-text:before {
    background-color: #a29778;
}

@media (max-width: 640px) {
    .checkbox-list {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .checkbox-list {
        grid-template-columns: 1fr;
    }
}

.filter-dropdown {
    margin-left: 10px;
    margin-top: 10px;
    display: none;
    position: absolute;
    background-color: rgba(36, 36, 36, 1);
    min-width: 400px;
    max-height: calc(100vh - 180px);
    overflow-y: auto;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 3;
    padding: 10px;
    padding-bottom: 20px;
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 15px,
        100% 100%,
        15px 100%,
        0 calc(100% - 15px)
    );
}

.filter-dropdown.show {
    display: block;
}

.filter-dropdown::-webkit-scrollbar {
    width: 8px;
}

.filter-dropdown::-webkit-scrollbar-track {
    background: rgba(36, 36, 36, 0.8);
}

.filter-dropdown::-webkit-scrollbar-thumb {
    background: #a29778;
    clip-path: polygon(
        0 0,
        calc(100% - 3px) 0,
        100% 3px,
        100% 100%,
        3px 100%,
        0 calc(100% - 3px)
    );
}

.filter-dropdown::-webkit-scrollbar-thumb:hover {
    background: #b3a88a;
}

@media (max-width: 640px) {
    .filter-dropdown {
        min-width: initial;
        width: calc(100vw - 40px);
        margin: 0 10px;
        margin-top: 10px;
        max-height: calc(100vh - 220px);
        padding-bottom: 20px;
    }
}

.filter-section {
    margin-bottom: 20px;
}

.filter-section:last-child {
    margin-bottom: 10px;
}

.filter-title {
    color: #a29778;
    font-size: 18px;
    margin-bottom: 10px;
    font-weight: 600;
}

#no-results-message {
    background-color: rgba(36, 36, 36, 0.8);
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    );
    margin: 10px;
    font-size: 16px;
}

.checkbox-item {
    transition: opacity 0.3s ease;
}

.checkbox-item input[type="checkbox"]:disabled + .checkbox-text {
    cursor: not-allowed;
}

/* Стили для SweetAlert2 */
.swal2-popup.swal2-toast {
    background-color: rgba(36, 36, 36, 0.9) !important;
    color: #f2f4f1 !important;
    padding: 1rem !important;
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    ) !important;
    display: flex !important;
    flex-direction: column !important;
}

.swal2-popup.swal2-toast .swal2-title {
    color: #a29778 !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    margin: 0 !important;
    padding-right: 20px !important;
}

.swal2-popup.swal2-toast .swal2-html-container {
    color: #f2f4f1 !important;
    margin: 5px 0 0 0 !important;
}

.swal2-popup.swal2-toast .swal2-close {
    position: absolute !important;
    right: 8px !important;
    top: 8px !important;
    color: #f2f4f1 !important;
    font-size: 16px !important;
}

.swal2-popup.swal2-toast .swal2-timer-progress-bar {
    background: #464b45 !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, что Swal доступен
    if (typeof Swal !== 'undefined') {
        console.log('SweetAlert2 is loaded');

        // Проверяем, не показывали ли мы уже уведомление
        if (!localStorage.getItem('redirectNotificationShown')) {
            Swal.fire({
                title: 'New Domain',
                text: 'The site has been permanently moved to new domain.',
                toast: true,
                position: 'bottom-end',
                showConfirmButton: false,
                showCloseButton: true,
                timer: 60000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    console.log('Toast opened');
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Отмечаем, что уведомление было показано
            localStorage.setItem('redirectNotificationShown', 'true');
        }
    } else {
        console.error('SweetAlert2 is not loaded');
    }
});
</script>

