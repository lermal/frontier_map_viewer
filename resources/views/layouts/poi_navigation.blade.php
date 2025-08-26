<script src="{{ asset('js/sweetalert2@11.js') }}"></script>

<nav class="navigation">
    <nav class="navbar">
        <div class="navbar-brand">
            <img src="{{ asset('images/Logo.webp') }}" alt="Frontier Station" width="140" height="62.06">
        </div>
        <div class="navbar-items">
            <a class="btn-primary" href="/"><i class="ri-rocket-2-fill"></i> Shipyard</a>
            <a class="btn-primary" href="https://discord.gg/frontier"><i class="ri-discord-fill"></i> Discord</a>
            <a class="btn-primary" href="https://frontierstation14.com/"><i class="ri-booklet-fill"></i> Wiki</a>
            <a class="btn-primary" href="https://github.com/new-frontiers-14/frontier-station-14"><i class="ri-github-fill"></i> Github</a>
        </div>
    </nav>
    <div class="dropdown">
        <div class="dropbtn__container">
            <button class="dropbtn btn-primary poi-toggle"><i class="ri-map-pin-2-fill"></i> Select POI</button>
        </div>
        <div class="dropdown-content">
            @foreach($groupedPoi as $letter => $points)
                <div class="dropdown-category">
                    <strong class="group-name">{{ $letter }}</strong>
                    <hr>
                    <ul class="poi-list">
                        @foreach($points as $poi)
                            <li class="poi-item"
                                data-id="{{ $poi['id'] }}"
                                data-name="{{ $poi['name'] }}"
                                data-category="{{ $poi['category'] ?? '' }}"
                                data-type="{{ $poi['type'] ?? '' }}">
                                <div class="poi-name">{{ $poi['name'] }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</nav>

<style>
.dropdown-content {
    display: none;
    position: absolute;
    background-color: rgba(36, 36, 36, 0.8);
    min-width: 300px;
    max-height: calc(100vh - 180px);
    overflow-y: auto;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 3;
    margin-top: 10px;
    margin-left: 10px;
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 15px,
        100% 100%,
        15px 100%,
        0 calc(100% - 15px)
    );
}

.dropdown-content.show {
    display: block;
}

.dropdown-content::-webkit-scrollbar {
    width: 8px;
}

.dropdown-content::-webkit-scrollbar-track {
    background: rgba(36, 36, 36, 0.8);
}

.dropdown-content::-webkit-scrollbar-thumb {
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

.dropdown-content::-webkit-scrollbar-thumb:hover {
    background: #b3a88a;
}

.dropdown-category {
    padding: 15px;
}

.group-name {
    color: #a29778;
    font-size: 18px;
    font-weight: 600;
    display: block;
    margin-bottom: 10px;
}

hr {
    border: none;
    border-top: 1px solid #464b45;
    margin: 10px 0;
}

.poi-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.poi-item {
    background-color: #464b45;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: background-color 0.2s;
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    );
}

.poi-item:hover {
    background-color: #a29778;
}

.poi-name {
    color: #f2f4f1;
    font-size: 16px;
}

@media (max-width: 640px) {
    .dropdown-content {
        min-width: initial;
        width: calc(100vw - 40px);
        margin: 0 10px;
        margin-top: 10px;
        max-height: calc(100vh - 220px);
    }
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
