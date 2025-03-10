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
                                data-file="{{ $poi['file_path'] }}">
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
    z-index: 1;
    margin-top: 10px;
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    );
}

.dropdown-content.show {
    display: block;
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

.poi-details-content {
    background-color: rgba(36, 36, 36, 0.8);
    padding: 20px;
    margin-top: 20px;
    clip-path: polygon(
        0 0,
        calc(100% - 10px) 0,
        100% 10px,
        100% 100%,
        10px 100%,
        0 calc(100% - 10px)
    );
}

.poi-file {
    color: #a29778;
    margin-bottom: 15px;
    font-size: 14px;
}

.poi-description {
    color: #f2f4f1;
    line-height: 1.6;
    font-size: 16px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poiToggle = document.querySelector('.poi-toggle');
    const dropdownContent = document.querySelector('.dropdown-content');
    const poiItems = document.querySelectorAll('.poi-item');
    const infoBlock = document.getElementById('info-block');
    const poiName = document.getElementById('poi-name');
    const poiInfoPanel = document.getElementById('poi-info-panel');
    const poiRender = document.getElementById('poi-render');
    const poiRenderContainer = document.getElementById('poi-render-container');

    let currentTransform = {
        x: 0,
        y: 0,
        scale: 1
    };

    let isDragging = false;
    let lastMouseX = 0;
    let lastMouseY = 0;

    function updateTransform() {
        const renderElement = document.getElementById('poi-render');
        if (renderElement) {
            renderElement.style.transform = `translate(${currentTransform.x}px, ${currentTransform.y}px) scale(${currentTransform.scale})`;
        }
    }

    if (poiRenderContainer) {
        // Масштабирование колесиком мыши
        poiRenderContainer.addEventListener('wheel', function(e) {
            e.preventDefault();
            const delta = e.deltaY * -0.001;
            const newScale = currentTransform.scale * (1 + delta);
            currentTransform.scale = Math.min(Math.max(newScale, 0.1), 5);
            updateTransform();
        }, { passive: false });

        // Перетаскивание мышью
        poiRenderContainer.addEventListener('mousedown', function(e) {
            isDragging = true;
            lastMouseX = e.clientX;
            lastMouseY = e.clientY;
            poiRenderContainer.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', function(e) {
            if (isDragging) {
                const deltaX = e.clientX - lastMouseX;
                const deltaY = e.clientY - lastMouseY;
                currentTransform.x += deltaX;
                currentTransform.y += deltaY;
                lastMouseX = e.clientX;
                lastMouseY = e.clientY;
                updateTransform();
            }
        });

        document.addEventListener('mouseup', function() {
            isDragging = false;
            poiRenderContainer.style.cursor = 'grab';
        });

        // Тач-события через interact.js
        interact(poiRenderContainer)
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
    }

    function loadPoiRender(poiId) {
        const renderWrapper = document.createElement('div');
        renderWrapper.id = 'poi-render';
        renderWrapper.style.position = 'relative';

        const container = document.getElementById('poi-render-container');
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

            // Создаем основной canvas
            const mainCanvas = document.createElement('canvas');
            mainCanvas.width = img.width;
            mainCanvas.height = img.height;
            const ctx = mainCanvas.getContext('2d');
            ctx.imageSmoothingEnabled = false;

            // Рисуем все изображение сразу на основной canvas
            ctx.drawImage(img, 0, 0, img.width, img.height);

            // Добавляем стили для canvas
            mainCanvas.style.imageRendering = 'pixelated';
            mainCanvas.style.imageRendering = 'crisp-edges';
            renderWrapper.appendChild(mainCanvas);

            // Создаем массив блоков для анимации
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
        };

        img.onerror = function() {
            console.error('Failed to load POI render');
            container.innerHTML = '<div class="error-message">Ошибка загрузки изображения</div>';
        };

        img.src = `/images/renders/${poiId}-0.png?v=${timestamp}`;
    }

    // Обработчик клика по POI
    poiItems.forEach(item => {
        item.addEventListener('click', async function() {
            const id = this.dataset.id;
            const name = this.dataset.name;

            // Сбрасываем трансформацию
            currentTransform = {
                x: 0,
                y: 0,
                scale: 1
            };

            window.history.pushState({}, '', `/poi?id=${id}`);

            if (infoBlock) {
                infoBlock.style.display = 'none';
            }

            if (poiInfoPanel) {
                poiInfoPanel.style.display = 'block';
            }

            if (poiName) {
                poiName.textContent = name;
            }

            loadPoiRender(id);
            dropdownContent.classList.remove('show');
        });
    });

    poiToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        dropdownContent.classList.toggle('show');
    });

    // Предотвращаем закрытие при клике внутри dropdown-content
    dropdownContent.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Закрытие dropdown при клике вне его области
    document.addEventListener('click', function(e) {
        if (!dropdownContent.contains(e.target) && !poiToggle.contains(e.target)) {
            dropdownContent.classList.remove('show');
        }
    });

    // Проверяем URL при загрузке страницы
    const urlParams = new URLSearchParams(window.location.search);
    const poiId = urlParams.get('id');
    if (poiId) {
        const poiItem = document.querySelector(`.poi-item[data-id="${poiId}"]`);
        if (poiItem) {
            poiItem.click();
        }
    }

    // Добавляем стили для правильного отображения
    poiRenderContainer.style.overflow = 'hidden';
    poiRender.style.transformOrigin = 'center center';

    // Добавляем стили
    const style = document.createElement('style');
    style.textContent = `
        #poi-render div {
            background: rgba(0, 0, 0, 0);
        }
        #poi-render canvas {
            display: block;
            image-rendering: pixelated;
            image-rendering: crisp-edges;
        }
    `;
    document.head.appendChild(style);
});
</script>
