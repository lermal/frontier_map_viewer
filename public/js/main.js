// Глобальная функция для обновления информации о шаттле
function updateShuttleInfo(item) {
    const shuttleName = document.getElementById('shuttle-name');
    const shuttlePrice = document.getElementById('shuttle-price');
    const shuttleDescription = document.getElementById('shuttle-description');
    const shuttleClass = document.getElementById('shuttle-class');
    const infoPanel = document.getElementById('shuttle-info-panel');

    if (shuttleName) shuttleName.textContent = item.getAttribute('data-name');
    if (shuttlePrice) shuttlePrice.textContent = new Intl.NumberFormat('ru-RU').format(item.getAttribute('data-price')) + ' spesos';
    if (shuttleDescription) shuttleDescription.textContent = item.getAttribute('data-description');

    // Обновляем классы
    if (shuttleClass) {
        const classes = item.getAttribute('data-class').split(', ');
        shuttleClass.innerHTML = classes
            .map(cls => `<span class="class-tag">${cls}</span>`)
            .join('');
    }

    // Показываем панель
    if (infoPanel) infoPanel.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', function () {
    const shuttleLinks = document.querySelectorAll('.shuttle-select');
    const shuttleRender = document.getElementById('shuttle-render');
    const container = document.getElementById('shuttle-render-container');
    const infoBlock = document.getElementById('info-block');

    // Элементы навигации и фильтров
    const filterToggle = document.querySelector('.filter-toggle');
    const shuttleToggle = document.querySelector('.shuttle-toggle');
    const filterDropdown = document.querySelector('.filter-dropdown');
    const dropdownContent = document.querySelector('.dropdown-content');
    const checkboxes = document.querySelectorAll('.shuttle-filter');
    const shuttleItems = Array.from(document.querySelectorAll('.shuttle-item'));

    let currentTransform = {
        x: 0,
        y: 0,
        rotate: 0,
        scale: 1
    };

    let isDragging = false;
    let lastMouseX = 0;
    let lastMouseY = 0;

    // Проверяем URL при загрузке страницы
    const urlParams = new URLSearchParams(window.location.search);
    const initialShuttleId = urlParams.get('shuttle');
    if (initialShuttleId && container && infoBlock) {
        const shuttleItem = document.querySelector(`.shuttle-item[data-id="${initialShuttleId}"]`);
        if (shuttleItem) {
            updateShuttleInfo(shuttleItem);
            loadShuttleRender(initialShuttleId);
            container.style.display = 'block';
            infoBlock.style.display = 'none';
        }
    } else if (container && infoBlock) {
        container.style.display = 'none';
        infoBlock.style.display = 'flex';
    }

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
            if (container) container.style.display = 'block';
            if (infoBlock) infoBlock.style.display = 'none';
        });
    });

    // Обновляем обработчик popstate
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const shuttleId = urlParams.get('shuttle');
        if (shuttleId && container && infoBlock) {
            const shuttleItem = document.querySelector(`.shuttle-item[data-id="${shuttleId}"]`);
            if (shuttleItem) {
                updateShuttleInfo(shuttleItem);
                loadShuttleRender(shuttleId);
                container.style.display = 'block';
                infoBlock.style.display = 'none';
            }
        } else if (container && infoBlock) {
            container.style.display = 'none';
            infoBlock.style.display = 'block';
        }
    });

    if (container) {
        container.addEventListener('wheel', function(event) {
            event.preventDefault();

            const scaleAmount = -event.deltaY * 0.001;

            currentTransform.scale *= (1 + scaleAmount);
            currentTransform.scale = Math.min(Math.max(currentTransform.scale, 0.1), 5);
            updateTransform();
        });
    }

    function loadShuttleRender(shuttleId) {
        if (!container) return;
        
        const timestamp = Date.now();
        const img = new Image();
        
        img.onload = function() {
            container.innerHTML = '';
            
            const renderWrapper = document.createElement('div');
            renderWrapper.id = 'shuttle-render';
            renderWrapper.style.position = 'relative';
            renderWrapper.style.width = img.width + 'px';
            renderWrapper.style.height = img.height + 'px';
            container.appendChild(renderWrapper);

            // Вычисляем начальное положение для центрирования
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;
            currentTransform.x = (windowWidth - img.width) / 2;
            currentTransform.y = (windowHeight - img.height) / 2;
            updateTransform();

            // Создаем основной canvas
            const mainCanvas = document.createElement('canvas');
            mainCanvas.width = img.width;
            mainCanvas.height = img.height;
            const ctx = mainCanvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            renderWrapper.appendChild(mainCanvas);

            // Создаем блоки для анимации загрузки
            const blockSize = 64;
            const blocks = [];

            for (let y = 0; y < img.height; y += blockSize) {
                for (let x = 0; x < img.width; x += blockSize) {
                    const width = Math.min(blockSize, img.width - x);
                    const height = Math.min(blockSize, img.height - y);

                    blocks.push({
                        x, y, width, height,
                        distance: Math.sqrt(x * x + y * y)
                    });
                }
            }

            // Сортируем блоки по расстоянию от центра
            blocks.sort((a, b) => a.distance - b.distance);

            // Вычисляем максимальное расстояние для нормализации задержки
            const maxDistance = blocks[blocks.length - 1].distance;
            const baseDelay = 20;

            // Анимируем блоки
            blocks.forEach((block, index) => {
                const delay = baseDelay * index;

                // Создаем блок затемнения
                const overlayDiv = document.createElement('div');
                overlayDiv.style.position = 'absolute';
                overlayDiv.style.left = block.x + 'px';
                overlayDiv.style.top = block.y + 'px';
                overlayDiv.style.width = block.width + 'px';
                overlayDiv.style.height = block.height + 'px';
                overlayDiv.style.backgroundColor = '#000';
                overlayDiv.style.transition = 'opacity 0.5s ease-out';
                overlayDiv.style.opacity = '1';
                overlayDiv.style.pointerEvents = 'none';
                renderWrapper.appendChild(overlayDiv);

                setTimeout(() => {
                    // Плавно убираем затемнение
                    overlayDiv.style.opacity = '0';

                    // Удаляем блок после анимации
                    setTimeout(() => {
                        overlayDiv.remove();
                    }, 500);
                }, delay);
            });

            // Показываем кнопки после загрузки
            showButtons(mainCanvas, shuttleId);
        };

        img.onerror = function() {
            console.error('Failed to load shuttle render');
            if (container) {
                const lowerShuttleId = shuttleId.toLowerCase();
                container.innerHTML = `
                    <div class="error-message">
                        <p>Ошибка загрузки изображения шаттла</p>
                        <p>Путь: /images/renders/${lowerShuttleId}/${lowerShuttleId}-0.png</p>
                        <p>Пожалуйста, проверьте наличие файла</p>
                    </div>`;
            }
        };

        const lowerShuttleId = shuttleId.toLowerCase();
        img.src = `/images/renders/${lowerShuttleId}/${lowerShuttleId}-0.png?v=${timestamp}`;
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

    function loadImageInBlocks(imageUrl) {
        const img = new Image();
        img.onload = function() {
            if (!container) return;
            
            const blockSize = 256;
            container.innerHTML = '';

            const renderWrapper = document.createElement('div');
            renderWrapper.id = 'shuttle-render';
            renderWrapper.style.position = 'relative';
            renderWrapper.style.width = img.width + 'px';
            renderWrapper.style.height = img.height + 'px';
            container.appendChild(renderWrapper);

            // Вычисляем начальное положение для центрирования
            const windowWidth = window.innerWidth;
            const windowHeight = window.innerHeight;
            currentTransform.x = (windowWidth - img.width) / 2;
            currentTransform.y = (windowHeight - img.height) / 2;
            updateTransform();

            // Остальной код создания блоков
            for (let y = 0; y < img.height; y += blockSize) {
                for (let x = 0; x < img.width; x += blockSize) {
                    const blockCanvas = document.createElement('canvas');
                    blockCanvas.width = blockSize;
                    blockCanvas.height = blockSize;
                    const blockCtx = blockCanvas.getContext('2d');

                    blockCtx.drawImage(img,
                        x, y, blockSize, blockSize,
                        0, 0, blockSize, blockSize
                    );

                    const blockDiv = document.createElement('div');
                    blockDiv.style.position = 'absolute';
                    blockDiv.style.left = x + 'px';
                    blockDiv.style.top = y + 'px';
                    blockDiv.appendChild(blockCanvas);

                    renderWrapper.appendChild(blockDiv);
                }
            }
        };
        img.src = imageUrl;
    }

    function resetTransformations() {
        currentTransform = {
            x: 0,
            y: 0,
            rotate: 0,
            scale: 1
        };
        updateTransform();
    }

    function updateTransform() {
        const renderElement = document.getElementById('shuttle-render');
        if (renderElement) {
            renderElement.style.transform = `translate(${currentTransform.x}px, ${currentTransform.y}px) rotate(${currentTransform.rotate}deg) scale(${currentTransform.scale})`;
        }
    }

    if (container) {
        interact('#shuttle-render-container')
            .gesturable({
                onmove: function (event) {
                    currentTransform.scale *= (1 + event.ds);
                    currentTransform.scale = Math.min(Math.max(currentTransform.scale, 0.1), 5);
                    updateTransform();
                }
            })
            .draggable({
                inertia: true,
                listeners: {
                    move(event) {
                        currentTransform.x += event.dx;
                        currentTransform.y += event.dy;
                        updateTransform();
                    }
                }
            });
    }

    // Отключаем стандартные жесты масштабирования
    document.addEventListener('gesturestart', function(e) {
        e.preventDefault();
    }, { passive: false });

    document.addEventListener('gesturechange', function(e) {
        e.preventDefault();
    }, { passive: false });

    document.addEventListener('gestureend', function(e) {
        e.preventDefault();
    }, { passive: false });

    if (container) {
        container.style.overflow = 'hidden';
    }
    
    const renderElement = document.getElementById('shuttle-render');
    if (renderElement) {
        renderElement.style.transformOrigin = 'center center';
    }

    // Добавляем стили в head документа
    const style = document.createElement('style');
    style.textContent = `
        #shuttle-render div {
            background: rgba(0, 0, 0, 0); /* Прозрачный фон для блоков */
        }
        #shuttle-render img {
            display: block;
            object-fit: cover;
        }
    `;
    document.head.appendChild(style);

    // Логика навигации и фильтрации
    if (filterToggle && shuttleToggle && filterDropdown && dropdownContent) {
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

        // Обработчик изменения чекбоксов
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
                if (noResultsMessage) {
                    noResultsMessage.style.display = visibleShuttlesCount === 0 ? 'block' : 'none';
                }
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
            if (!filterDropdown.contains(e.target) && !filterToggle.contains(e.target)) {
                filterDropdown.classList.remove('show');
            }
            if (!dropdownContent.contains(e.target) && !shuttleToggle.contains(e.target)) {
                dropdownContent.classList.remove('show');
            }
        });
    }
});
