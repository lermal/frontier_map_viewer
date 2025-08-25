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

        const poiItem = document.querySelector(`.poi-item[data-id="${poiId}"]`);
        const name = poiItem.dataset.name;
        const filePath = poiItem.dataset.file;

        // Сначала получаем метаданные POI
        fetch(`/api/poi/${poiId}`)
            .then(response => response.json())
            .then(poiData => {
                if (poiData.error) {
                    throw new Error(poiData.error);
                }
                return fetch(`/api/poi/${encodeURIComponent(poiId)}/image`);
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }

                renderWrapper.style.width = data.width + 'px';
                renderWrapper.style.height = data.height + 'px';

                // Создаем финальный canvas
                const finalCanvas = document.createElement('canvas');
                finalCanvas.width = data.width;
                finalCanvas.height = data.height;
                finalCanvas.style.imageRendering = 'pixelated';
                const ctx = finalCanvas.getContext('2d');
                ctx.imageSmoothingEnabled = false;

                // Центрируем изображение
                const containerRect = container.getBoundingClientRect();
                currentTransform.x = (containerRect.width - data.width) / 2;
                currentTransform.y = (containerRect.height - data.height) / 2;
                updateTransform();

                // Сортируем блоки от центра
                const centerX = data.width / 2;
                const centerY = data.height / 2;
                const blocks = data.blocks.sort((a, b) => {
                    const distA = Math.sqrt(Math.pow(a.x + a.width/2 - centerX, 2) + Math.pow(a.y + a.height/2 - centerY, 2));
                    const distB = Math.sqrt(Math.pow(b.x + b.width/2 - centerX, 2) + Math.pow(b.y + b.height/2 - centerY, 2));
                    return distA - distB;
                });

                // Создаем массив промисов для загрузки всех изображений
                const loadPromises = blocks.map(block => {
                    return new Promise((resolve, reject) => {
                        const blockImg = new Image();
                        blockImg.onload = () => {
                            ctx.drawImage(blockImg, block.x, block.y, block.width, block.height);
                            resolve();
                        };
                        blockImg.onerror = () => reject(new Error(`Failed to load block at ${block.url}`));
                        blockImg.src = block.url;
                    });
                });

                // Добавляем canvas в DOM сразу
                renderWrapper.appendChild(finalCanvas);

                // Загружаем все блоки
                Promise.all(loadPromises)
                    .catch(error => {
                        console.error('Failed to load some blocks:', error);
                    });
            })
            .catch(error => {
                console.error('Failed to load POI:', error);
                container.innerHTML = `<div class="error-message">Ошибка загрузки изображения: ${error.message}</div>`;
            });
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

    // Добавляем стили
    const style = document.createElement('style');
    style.textContent = `
        #poi-render-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            position: relative;
        }
        #poi-render {
            transform-origin: center center;
            position: absolute;
        }
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
