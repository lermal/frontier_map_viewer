function updatePoiInfo(item) {
    const poiName = document.getElementById('poi-name');
    
    if (poiName) {
        poiName.textContent = item.dataset.name;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const poiItems = document.querySelectorAll('.poi-item');
    const poiRender = document.getElementById('poi-render');
    const container = document.getElementById('poi-render-container');
    const infoBlock = document.getElementById('info-block');
    const poiInfoPanel = document.getElementById('poi-info-panel');

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
    const initialPoiId = urlParams.get('poi');
    if (initialPoiId && container && infoBlock) {
        const poiItem = document.querySelector(`.poi-item[data-id="${initialPoiId}"]`);
        if (poiItem) {
            updatePoiInfo(poiItem);
            loadPoiRender(initialPoiId);
            container.style.display = 'block';
            infoBlock.style.display = 'none';
            if (poiInfoPanel) {
                poiInfoPanel.style.display = 'block';
            }
        }
    } else if (container && infoBlock) {
        container.style.display = 'none';
        infoBlock.style.display = 'flex';
        if (poiInfoPanel) {
            poiInfoPanel.style.display = 'none';
        }
    }

    // Обновляем обработчик клика по poi-item
    poiItems.forEach(item => {
        item.addEventListener('click', function() {
            const poiId = this.dataset.id;
            
            // Сбрасываем трансформацию
            currentTransform = {
                x: 0,
                y: 0,
                rotate: 0,
                scale: 1
            };

            updatePoiInfo(this);
            
            // Обновляем URL
            window.history.pushState({}, '', `/poi?poi=${poiId}`);
            
            loadPoiRender(poiId);
            
            if (container && infoBlock) {
                container.style.display = 'block';
                infoBlock.style.display = 'none';
            }
            
            if (poiInfoPanel) {
                poiInfoPanel.style.display = 'block';
            }

            // Закрываем dropdown
            const dropdownContent = document.querySelector('.dropdown-content');
            if (dropdownContent) {
                dropdownContent.classList.remove('show');
            }
        });
    });

    function updateTransform() {
        const renderElement = document.getElementById('poi-render');
        if (renderElement) {
            renderElement.style.transform = `translate(${currentTransform.x}px, ${currentTransform.y}px) rotate(${currentTransform.rotate}deg) scale(${currentTransform.scale})`;
        }
    }

    // Обработчики мыши и клавиатуры для интерактивности
    if (container) {
        // Масштабирование колесиком мыши
        container.addEventListener('wheel', function(e) {
            e.preventDefault();
            const delta = e.deltaY * -0.001;
            const newScale = currentTransform.scale * (1 + delta);
            currentTransform.scale = Math.min(Math.max(newScale, 0.1), 5);
            updateTransform();
        }, { passive: false });

        // Перетаскивание мышью
        container.addEventListener('mousedown', function(e) {
            isDragging = true;
            lastMouseX = e.clientX;
            lastMouseY = e.clientY;
            container.style.cursor = 'grabbing';
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
            container.style.cursor = 'grab';
        });

        // Поворот с помощью клавиш
        document.addEventListener('keydown', function(e) {
            if (e.code === 'KeyQ') {
                currentTransform.rotate -= 90;
                updateTransform();
            } else if (e.code === 'KeyE') {
                currentTransform.rotate += 90;
                updateTransform();
            }
        });
    }

    function loadPoiRender(poiId) {
        if (!container) return;
        
        const timestamp = Date.now();
        const img = new Image();
        
        img.onload = function() {
            container.innerHTML = '';
            
            const renderWrapper = document.createElement('div');
            renderWrapper.id = 'poi-render';
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
            mainCanvas.style.imageRendering = 'pixelated';
            mainCanvas.style.imageRendering = 'crisp-edges';
            const ctx = mainCanvas.getContext('2d');
            ctx.imageSmoothingEnabled = false;
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
        };

        img.onerror = function() {
            console.error('Failed to load POI render');
            if (container) {
                const lowerPoiId = poiId.toLowerCase();
                container.innerHTML = `
                    <div class="error-message">
                        <p>Ошибка загрузки изображения POI</p>
                        <p>Путь: /images/renders/${lowerPoiId}/${lowerPoiId}-0.png</p>
                        <p>Пожалуйста, проверьте наличие файла</p>
                    </div>`;
            }
        };

        const lowerPoiId = poiId.toLowerCase();
        img.src = `/images/renders/${lowerPoiId}/${lowerPoiId}-0.png?v=${timestamp}`;
    }

    // Dropdown functionality
    const poiToggle = document.querySelector('.poi-toggle');
    const dropdownContent = document.querySelector('.dropdown-content');

    if (poiToggle && dropdownContent) {
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
    }
}); 