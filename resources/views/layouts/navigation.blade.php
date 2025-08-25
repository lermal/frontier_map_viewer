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
    // Добавим отладочный вывод
    console.log('Document loaded');
    console.log('Referrer:', document.referrer);

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

    const filterToggle = document.querySelector('.filter-toggle');
    const shuttleToggle = document.querySelector('.shuttle-toggle');
    const filterDropdown = document.querySelector('.filter-dropdown');
    const dropdownContent = document.querySelector('.dropdown-content');
    const checkboxes = document.querySelectorAll('.shuttle-filter');
    const shuttleItems = Array.from(document.querySelectorAll('.shuttle-item'));

    // Обработчик для кнопки выбора шаттла
    shuttleToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownContent.classList.toggle('show');
        // Закрываем фильтры если открыты
        filterDropdown.classList.remove('show');
    });

    // Обработчик для кнопки фильтров
    filterToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        filterDropdown.classList.toggle('show');

        dropdownContent.classList.remove('show');
        // Оставляем список шаттлов открытым если он был открыт
    });

    // Функция для получения доступных комбинаций
    function getAvailableFilters() {
        const combinations = new Map();

        shuttleItems.forEach(item => {
            const category = item.dataset.category;
            const classes = item.dataset.class.split(', ');
            const engines = item.dataset.engines.split(', ');

            // Добавляем комбинации для классов
            classes.forEach(cls => {
                if (!combinations.has(cls)) {
                    combinations.set(cls, new Set());
                }
                combinations.get(cls).add(category);
            });

            // Добавляем комбинации для двигателей
            engines.forEach(engine => {
                if (!combinations.has(engine)) {
                    combinations.set(engine, new Set());
                }
                combinations.get(engine).add(category);
            });
        });

        return combinations;
    }

    // Функция обновления состояния чекбоксов
    function updateCheckboxStates() {
        const combinations = getAvailableFilters();
        const selectedCategories = Array.from(checkboxes)
            .filter(cb => cb.checked && cb.dataset.filterType === 'category')
            .map(cb => cb.value);

        const selectedClasses = Array.from(checkboxes)
            .filter(cb => cb.checked && cb.dataset.filterType === 'class')
            .map(cb => cb.value);

        const selectedEngines = Array.from(checkboxes)
            .filter(cb => cb.checked && cb.dataset.filterType === 'engine')
            .map(cb => cb.value);

        // Обновляем доступность чекбоксов
        checkboxes.forEach(checkbox => {
            let isAvailable = true;

            if (checkbox.dataset.filterType === 'class') {
                if (selectedCategories.length > 0) {
                    isAvailable = selectedCategories.some(category => {
                        return combinations.has(checkbox.value) &&
                               combinations.get(checkbox.value).has(category);
                    });
                }
                if (selectedEngines.length > 0 && isAvailable) {
                    isAvailable = shuttleItems.some(item => {
                        const itemEngines = item.dataset.engines.split(', ');
                        const itemClasses = item.dataset.class.split(', ');
                        return itemClasses.includes(checkbox.value) &&
                               selectedEngines.some(engine => itemEngines.includes(engine));
                    });
                }
            }

            if (checkbox.dataset.filterType === 'category') {
                if (selectedClasses.length > 0) {
                    isAvailable = selectedClasses.some(cls => {
                        return combinations.has(cls) &&
                               combinations.get(cls).has(checkbox.value);
                    });
                }
                if (selectedEngines.length > 0 && isAvailable) {
                    isAvailable = shuttleItems.some(item => {
                        const itemEngines = item.dataset.engines.split(', ');
                        return item.dataset.category === checkbox.value &&
                               selectedEngines.some(engine => itemEngines.includes(engine));
                    });
                }
            }

            if (checkbox.dataset.filterType === 'engine') {
                if (selectedCategories.length > 0) {
                    isAvailable = shuttleItems.some(item => {
                        const itemEngines = item.dataset.engines.split(', ');
                        return itemEngines.includes(checkbox.value) &&
                               selectedCategories.includes(item.dataset.category);
                    });
                }
                if (selectedClasses.length > 0 && isAvailable) {
                    isAvailable = shuttleItems.some(item => {
                        const itemEngines = item.dataset.engines.split(', ');
                        const itemClasses = item.dataset.class.split(', ');
                        return itemEngines.includes(checkbox.value) &&
                               selectedClasses.some(cls => itemClasses.includes(cls));
                    });
                }
            }

            checkbox.disabled = !isAvailable;
            checkbox.parentElement.style.opacity = checkbox.disabled ? '0.5' : '1';
        });
    }

    // Обновляем обработчик изменения чекбоксов
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const selectedClasses = Array.from(checkboxes)
                .filter(cb => cb.checked && cb.dataset.filterType === 'class')
                .map(cb => cb.value);

            const selectedCategories = Array.from(checkboxes)
                .filter(cb => cb.checked && cb.dataset.filterType === 'category')
                .map(cb => cb.value);

            const selectedEngines = Array.from(checkboxes)
                .filter(cb => cb.checked && cb.dataset.filterType === 'engine')
                .map(cb => cb.value);

            let visibleShuttlesCount = 0;

            shuttleItems.forEach(item => {
                const shuttleClasses = item.dataset.class.split(', ');
                const shuttleCategory = item.dataset.category;
                const shuttleEngines = item.dataset.engines.split(', ');

                const matchesClass = selectedClasses.length === 0 ||
                    shuttleClasses.some(cls => selectedClasses.includes(cls));

                const matchesCategory = selectedCategories.length === 0 ||
                    selectedCategories.includes(shuttleCategory);

                const matchesEngine = selectedEngines.length === 0 ||
                    shuttleEngines.some(engine => selectedEngines.includes(engine));

                const shouldShow = matchesClass && matchesCategory && matchesEngine;
                item.style.display = shouldShow ? '' : 'none';
                if (shouldShow) visibleShuttlesCount++;
            });

            // Обновляем состояние чекбоксов
            updateCheckboxStates();

            // Скрываем пустые категории
            document.querySelectorAll('.dropdown-category').forEach(category => {
                const visibleShuttles = category.querySelectorAll('.shuttle-item[style="display: block"], .shuttle-item:not([style*="display: none"])');
                category.style.display = visibleShuttles.length > 0 ? 'block' : 'none';
            });

            // Показываем/скрываем сообщение об отсутствии результатов
            const noResultsMessage = document.getElementById('no-results-message');
            noResultsMessage.style.display = visibleShuttlesCount === 0 ? 'block' : 'none';
        });
    });

    // Инициализируем состояния чекбоксов при загрузке
    updateCheckboxStates();

    // Предотвращаем закрытие при клике внутри dropdown-content
    dropdownContent.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Предотвращаем закрытие при клике внутри filter-dropdown
    filterDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Закрытие dropdowns при клике вне их области
    document.addEventListener('click', function(e) {
        if (!filterDropdown.contains(e.target) && !shuttleToggle.contains(e.target)) {
            filterDropdown.classList.remove('show');
        }
        if (!dropdownContent.contains(e.target) && !filterToggle.contains(e.target)) {
            dropdownContent.classList.remove('show');
        }
    });

    // Обновляем обработчик клика по shuttle-item
    shuttleItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const shuttleId = this.getAttribute('data-id');

            // Обновляем URL
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('shuttle', shuttleId);
            history.pushState({}, '', newUrl);

            // Обновляем информацию и загружаем рендер
            updateShuttleInfo(this);
            loadShuttleRender(shuttleId);
            container.style.display = 'block';
            infoBlock.style.display = 'none';
        });
    });

    function loadShuttleRender(shuttleId) {
        const renderWrapper = document.createElement('div');
        renderWrapper.id = 'shuttle-render';
        renderWrapper.style.position = 'relative';

        const container = document.getElementById('shuttle-render-container');
        container.innerHTML = '';
        container.appendChild(renderWrapper);

        const timestamp = new Date().getTime();
        const blockSize = 256;
        const img = new Image();

        img.onload = function() {
            renderWrapper.style.width = img.width + 'px';
            renderWrapper.style.height = img.height + 'px';

            // Центрируем изображение
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;
            currentTransform.x = (windowWidth - img.width) / 2;
            currentTransform.y = (windowHeight - img.height) / 2;
            updateTransform();

            // Создаем основной canvas для изображения
            const mainCanvas = document.createElement('canvas');
            mainCanvas.width = img.width;
            mainCanvas.height = img.height;
            const mainCtx = mainCanvas.getContext('2d');
            mainCtx.imageSmoothingEnabled = false;

            // Создаем canvas для затемнения
            const overlayCanvas = document.createElement('canvas');
            overlayCanvas.width = img.width;
            overlayCanvas.height = img.height;
            overlayCanvas.style.position = 'absolute';
            overlayCanvas.style.left = '0';
            overlayCanvas.style.top = '0';
            const overlayCtx = overlayCanvas.getContext('2d');

            // Рисуем изображение на основном canvas
            mainCtx.drawImage(img, 0, 0);
            mainCanvas.style.imageRendering = 'pixelated';
            mainCanvas.style.imageRendering = 'crisp-edges';

            // Заполняем overlay черным цветом
            overlayCtx.fillStyle = '#000';
            overlayCtx.fillRect(0, 0, overlayCanvas.width, overlayCanvas.height);

            // Добавляем оба canvas в wrapper
            renderWrapper.appendChild(mainCanvas);
            renderWrapper.appendChild(overlayCanvas);

            // Создаем массив блоков для анимации
            const blocks = [];
            for (let y = 0; y < img.height; y += blockSize) {
                for (let x = 0; x < img.width; x += blockSize) {
                    const width = Math.min(blockSize, img.width - x);
                    const height = Math.min(blockSize, img.height - y);

                    blocks.push({
                        x, y, width, height,
                        distance: Math.sqrt(
                            Math.pow(x + width/2 - img.width/2, 2) +
                            Math.pow(y + height/2 - img.height/2, 2)
                        )
                    });
                }
            }

            // Сортируем блоки по расстоянию от центра
            blocks.sort((a, b) => a.distance - b.distance);

            // Анимируем блоки
            const baseDelay = 20;
            blocks.forEach((block, index) => {
                setTimeout(() => {
                    // Очищаем область блока в overlay canvas
                    overlayCtx.clearRect(block.x, block.y, block.width, block.height);
                }, baseDelay * index);
            });

            // Показываем кнопки после загрузки
            showButtons(mainCanvas, shuttleId);
        };

        img.onerror = function() {
            console.error('Failed to load shuttle render');
            container.innerHTML = '<div class="error-message">Ошибка загрузки изображения</div>';
        };

        img.src = `/images/shuttles/${shuttleId}.webp?v=${timestamp}`;
    }

    function showButtons(canvas, shuttleId) {
        const buttonContainer = document.createElement('div');
        buttonContainer.className = 'download-buttons';
        buttonContainer.style.position = 'fixed';
        buttonContainer.style.bottom = '10px';
        buttonContainer.style.right = '10px';
        buttonContainer.style.zIndex = '2';
        buttonContainer.style.padding = '10px';
        buttonContainer.style.backgroundColor = 'rgba(36, 36, 36, 0.8)';
        buttonContainer.style.borderRadius = '8px';
    }
});
</script>

