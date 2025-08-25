# -*- coding: utf-8 -*-

import yaml
import json
import os
import subprocess
import time
from pathlib import Path
import shutil
import sys

# Регистрируем конструкторы YAML до любого использования
def nanotrasen_name_constructor(loader, node):
    if isinstance(node, yaml.MappingNode):
        data = loader.construct_mapping(node)
        data['type'] = 'NanotrasenNameGenerator'
        return data
    elif isinstance(node, yaml.ScalarNode):
        value = loader.construct_scalar(node)
        return {'type': 'NanotrasenNameGenerator', 'prefixCreator': value}
    return {'type': 'NanotrasenNameGenerator'}

# Регистрируем конструкторы
yaml.SafeLoader.add_constructor('!type:NanotrasenNameGenerator', nanotrasen_name_constructor)
yaml.SafeLoader.add_constructor('!type:SoundPathSpecifier',
    lambda loader, node: str(loader.construct_scalar(node)))

print('aaaaaaaaaaaaaaaaaaaaaaaaa2')

# Метод для добавления кастомных тегов
def type_tag(name):
    return {
        'tag': f'!type:{name}',
        'collection': 'map',
        'identify': lambda: False,
        'resolve': lambda value: add_custom_tag(value, name)
    }

def add_custom_tag(value, name):
    if not isinstance(value, dict):  # Проверяем, что это словарь
        raise ValueError(f'Expected dict, got {type(value)}')
    value['!type'] = name  # Добавляем кастомный тег

# Пример использования
custom_tags = [
    'SatiateThirst',
    'OrganType',
    'AdjustReagent',
    'HealthChange',
    'PlantAdjustWater',
    'FootstepBlood',
    'InsectBlood',
    'SpillTileReaction',
    'SatiateHunger',
    'ChemVomit',
    'ModifyBloodLevel',
    'Oxygenate',
    'ModifyLungGas',
    'PopupMessage',
    'CreateEntityTileReaction',
    'FlammableTileReaction',
    'ReagentThreshold',
    'AdjustAlert',
    'ModifyBleedAmount',
    'PlantAdjustNutrition',
    'PlantAdjustHealth',
    'PlantAdjustMutationMod',
    'PlantAdjustToxins',
    'PlantAdjustPests',
    'PlantAdjustWeeds',
    'RobustHarvest',
    'PlantRestoreSeeds',
    'PlantAdjustPotency',
    'PlantAffectGrowth',
    'PlantDiethylamine',
    'Polymorph',
    'ChemCleanBloodstream',
    'NFActivateArtifact',
    'GenericStatusEffect',
    'CleanTileReaction',
    'CleanDecalsReaction',
    'Emote',
    'PlantAdjustMutationLevel',
    'ExtinguishTileReaction',
    'Electrocute',
    'AdjustTemperature',
    'ExtinguishReaction',
    'MovespeedModifier',
    'HasTag',
    'Temperature',
    'FlammableReaction',
    'Drunk',
    'PlantCryoxadone',
    'PlantPhalanximine',
    'Jitter',
    'CureZombieInfection',
    'ChemHealEyeDamage',
    'MakeSentient',
    'ResetNarcolepsy',
    'ReduceRotting',
    'MobStateCondition',
    'TotalDamage',
    'ExplosionReactionEffect',
    'AreaReactionEffect',
    'EmpReactionEffect',
    'FlashReactionEffect',
    'CreateEntityReactionEffect',
    'CreateGas',
    'CauseZombieInfection',
    'Hunger',
    'SpillIfPuddlePresentTileReaction'
]

# Функция для конвертации YAML в данные
def convert_yaml_to_data(yaml_file):
    with open(yaml_file, 'r', encoding='utf-8') as file:
        data = yaml.load(file, Loader=yaml.FullLoader)
        return data

def run_command(command):
    try:
        subprocess.run(command, shell=True, check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        return True
    except subprocess.CalledProcessError:
        return False

def git_pull():
    print("Выполняется git pull...")
    return run_command("git pull")

def check_map_needs_render(map_id):
    # Список возможных вариантов имен папок
    possible_paths = [
        Path(f"Resources/MapImages/{map_id}"),  # Полный ID (например, CorvaxOutpost)
    ]

    # Если ID начинается с Corvax, добавляем вариант без префикса
    if map_id.startswith("Corvax"):
        base_name = map_id[6:]  # Убираем 'Corvax' из начала
        possible_paths.append(Path(f"Resources/MapImages/{base_name}"))

    # Проверяем все возможные пути
    for path in possible_paths:
        if path.exists() and any(path.glob("*.png")):
            print(f"Найдены существующие рендеры для {map_id} в {path}")
            return False

    print(f"Рендеры не найдены для {map_id}. Проверены пути: {[str(p) for p in possible_paths]}")
    return True

def retry_render(map_id, map_data=None):
    print("Ошибка рендера. Пробуем перестроить проект...")

    if run_command("python RUN_THIS.py"):
        print("RUN_THIS.py выполнен успешно")
    else:
        print("Ошибка при выполнении RUN_THIS.py")

    if run_command("dotnet build"):
        print("dotnet build выполнен успешно")
    else:
        print("Ошибка при выполнении dotnet build")

    time.sleep(2)
    command = f'dotnet run --project Content.MapRenderer {map_id}'
    if run_command(command):
        print(f"Успешный рендер {map_id} после повторной попытки")
        return True

    # Если есть map_data и рендер по id не удался, пробуем по stations
    if map_data and 'stations' in map_data and map_data['stations']:
        for station_name in map_data['stations']:
            print(f"Пробуем рендер по station name: {station_name}")
            command = f'dotnet run --project Content.MapRenderer {station_name}'
            if run_command(command):
                print(f"Успешный рендер {map_id} (через station {station_name})")
                return True

    print(f"Рендер {map_id} не удался даже после повторных попыток")
    return False

def copy_renders_to_web(map_id):
    print(f"\n=== Начало копирования рендеров для {map_id} ===")

    possible_source_dirs = [
        Path(f"Resources/MapImages/{map_id}"),
    ]

    if map_id.startswith("Corvax"):
        base_name = map_id[6:]
        possible_source_dirs.append(Path(f"Resources/MapImages/{base_name}"))
        print(f"Добавлен дополнительный путь для Corvax карты: {base_name}")

    print("\nПроверяемые исходные директории:")
    for source_dir in possible_source_dirs:
        print(f"- {source_dir} {'(существует)' if source_dir.exists() else '(не существует)'}")

    dest_dir = Path("/var/www/www-root/data/www/shipyard.webcodewizard.ru/public/images/renders")
    print(f"\nЦелевая директория: {dest_dir}")
    print(f"Целевая директория существует: {dest_dir.exists()}")

    try:
        dest_dir.mkdir(parents=True, exist_ok=True)
        print("Целевая директория создана/проверена успешно")
    except Exception as e:
        print(f"Ошибка при создании целевой директории: {e}")

    copied = False
    for source_dir in possible_source_dirs:
        if source_dir.exists():
            print(f"\nОбработка директории: {source_dir}")
            png_files = list(source_dir.glob("*.png"))
            print(f"Найдено PNG файлов: {len(png_files)}")

            for source_path in png_files:
                try:
                    # Используем оригинальное имя файла
                    dest_path = dest_dir / source_path.name
                    print(f"\nКопирование:")
                    print(f"Из: {source_path}")
                    print(f"В: {dest_path}")

                    print(f"Права доступа к исходному файлу: {oct(source_path.stat().st_mode)[-3:]}")
                    if dest_path.exists():
                        print(f"Права доступа к существующему целевому файлу: {oct(dest_path.stat().st_mode)[-3:]}")

                    shutil.copy2(source_path, dest_path)
                    print(f"✅ Успешно скопировано: {source_path.name}")
                    copied = True
                except Exception as e:
                    print(f"❌ Ошибка при копировании {source_path.name}: {str(e)}")
                    print(f"Тип ошибки: {type(e).__name__}")
        else:
            print(f"\nДиректория не существует: {source_dir}")

    if not copied:
        print(f"\n❌ Ошибка: не найдены изображения для {map_id}")
        print(f"Проверены пути: {[str(p) for p in possible_source_dirs]}")
        return False

    print(f"\n=== Завершено копирование рендеров для {map_id} ===")
    return True

def print_existing_renders():
    map_images = Path("Resources/MapImages")
    if map_images.exists():
        print("\nСуществующие рендеры:")
        for folder in map_images.iterdir():
            if folder.is_dir():
                png_files = list(folder.glob("*.png"))
                if png_files:
                    print(f"- {folder.name} ({len(png_files)} файлов)")
    else:
        print("\nДиректория MapImages не существует!")
    print()

# Список игнорируемых карт
IGNORED_MAPS = {
    'Empty',
    'TEG',
    'Arena',
    'Dev',
    'TestTeg',
    'MeteorArena',
    'CentComm'
}

def save_maps_json(combined_data, base_directory):
    # Сохраняем локальную копию
    local_json_path = os.path.join(base_directory, 'maps.json')
    with open(local_json_path, 'w', encoding='utf-8') as json_file:
        json.dump(combined_data, json_file, indent=4, ensure_ascii=False)
    print(f'Обновлен: {local_json_path}')

    # Сохраняем копию в веб-директории
    web_json_path = "/var/www/www-root/data/www/shipyard.webcodewizard.ru/storage/app/Maps/maps.json"
    web_json_dir = os.path.dirname(web_json_path)

    try:
        # Создаем директорию, если её нет
        os.makedirs(web_json_dir, exist_ok=True)

        # Копируем файл
        with open(web_json_path, 'w', encoding='utf-8') as json_file:
            json.dump(combined_data, json_file, indent=4, ensure_ascii=False)
        print(f'Обновлен: {web_json_path}')
        return True
    except Exception as e:
        print(f"ОШИБКА при сохранении {web_json_path}: {e}")
        return False

def process_yaml_files(base_directory):
    print_existing_renders()

    if not git_pull():
        print("Ошибка при выполнении git pull")
        return

    combined_data = []
    maps_to_render = []

    # Определяем пути к картам
    map_paths = [
        os.path.join(base_directory, 'Resources/Prototypes/Maps'),
        os.path.join(base_directory, 'Resources/Prototypes/Corvax/Maps/Corvax')
    ]

    print("\nПроверяемые директории:")
    for path in map_paths:
        if os.path.exists(path):
            print(f"✓ {path} (существует)")
            # Показываем содержимое директории
            print("  Содержимое:")
            try:
                for file in os.listdir(path):
                    if file.endswith(('.yml', '.yaml')):
                        print(f"  - {file}")
            except Exception as e:
                print(f"  Ошибка при чтении директории: {e}")
        else:
            print(f"✗ {path} (не найдена)")
    print()

    old_data = {}
    json_path = os.path.join(base_directory, 'maps.json')
    if os.path.exists(json_path):
        with open(json_path, 'r', encoding='utf-8') as f:
            try:
                old_data = {item['mapPath']: item for item in json.load(f)}
            except json.JSONDecodeError:
                old_data = {}

    # Собираем все yaml файлы из обеих директорий
    yaml_files = []
    for path in map_paths:
        if not os.path.exists(path):
            continue

        try:
            for filename in os.listdir(path):
                if filename.endswith(('.yml', '.yaml')):
                    full_path = os.path.join(path, filename)
                    yaml_files.append(full_path)
                    print(f"Добавлен файл: {full_path}")
        except Exception as e:
            print(f"Ошибка при чтении директории {path}: {e}")

    print(f"\nНайдено {len(yaml_files)} yaml файлов для обработки:")
    for f in yaml_files:
        print(f"- {f}")

    # Обрабатываем каждый yaml файл
    for yaml_file_path in yaml_files:
        print(f"\nОбработка файла: {yaml_file_path}")
        try:
            data = convert_yaml_to_data(yaml_file_path)
            print(f"Данные загружены успешно, найдено {len(data) if isinstance(data, list) else 0} элементов")
        except Exception as e:
            print(f"Ошибка при загрузке файла {yaml_file_path}: {e}")
            continue

        if data and isinstance(data, list):
            for item in data:
                if isinstance(item, dict) and item.get('type') == 'gameMap':
                    map_data = item.copy()
                    print(f"Найдена карта: {map_data.get('id')} в файле {yaml_file_path}")

                    # Пропускаем игнорируемые карты
                    if map_data.get('id') in IGNORED_MAPS:
                        print(f"Пропущена игнорируемая карта: {map_data.get('id')}")
                        continue

                    # Обработка stations
                    if 'stations' in map_data:
                        if isinstance(map_data['stations'], dict):
                            station_names = list(map_data['stations'].keys())
                            map_data['stations'] = station_names
                            print(f"Карта {map_data['id']}: найдены станции {station_names}")
                        elif isinstance(map_data['stations'], str):
                            map_data['stations'] = [map_data['stations']]
                        else:
                            print(f"Предупреждение: Неожиданный формат stations для {map_data.get('id')}")
                            map_data['stations'] = []

                    needs_render = False
                    if map_data['mapPath'] in old_data:
                        old_map = old_data[map_data['mapPath']]
                        if old_map != map_data:
                            print(f"Изменения обнаружены для {map_data['id']}")
                            needs_render = True
                    else:
                        print(f"Новая карта: {map_data['id']}")
                        needs_render = True

                    if check_map_needs_render(map_data['id']):
                        print(f"Рендер не найден для {map_data['id']}")
                        needs_render = True

                    if needs_render:
                        maps_to_render.append(map_data['id'])
                        print(f"Добавлен в очередь рендера: {map_data['id']}")

                    combined_data.append(map_data)
                    print(f"Добавлена карта: {map_data['id']}")

    # Сохраняем maps.json в обоих местах
    if not save_maps_json(combined_data, base_directory):
        print("❌ ОШИБКА: Не удалось сохранить maps.json")
        return False

    # Копируем все карты в веб-директорию
    print("\nКопирование всех карт в веб-директорию...")
    failed_copies = []
    for map_data in combined_data:
        map_id = map_data['id']
        if map_id in IGNORED_MAPS:
            continue

        if not copy_renders_to_web(map_id):
            failed_copies.append(map_id)

    if failed_copies:
        print("\n⚠️ Предупреждение: Не удалось скопировать следующие карты:")
        for map_id in failed_copies:
            print(f"- {map_id}")

    # Продолжаем с рендером новых/измененных карт
    if not maps_to_render:
        print("\nНет карт для рендера")
        return True

    print(f"\nНеобходимо отрендерить {len(maps_to_render)} карт:")
    for map_id in maps_to_render:
        print(f"- {map_id}")

    failed_maps = []
    for map_id in maps_to_render:
        map_data = next((item for item in combined_data if item['id'] == map_id), None)

        command = f'dotnet run --project Content.MapRenderer {map_id}'
        print(f'\nВыполняется: {command}')

        render_success = False
        if not run_command(command):
            print(f"Ошибка при рендере {map_id}, пробуем восстановить...")
            if not retry_render(map_id, map_data):
                print(f"ОШИБКА: Не удалось отрендерить карту {map_id}")
                failed_maps.append(map_id)
                continue
            else:
                render_success = True
        else:
            render_success = True

        # Копируем изображение после рендера
        if render_success:
            if not copy_renders_to_web(map_id):
                print(f"ОШИБКА: Не удалось скопировать изображения для {map_id}")
                failed_maps.append(map_id)

    if failed_maps:
        print("\n❌ ОШИБКА: Следующие карты не удалось обработать:")
        for map_id in failed_maps:
            print(f"- {map_id}")
        print("\nТребуется ручное вмешательство!")
        return False
    else:
        print("\n✅ Все необходимые карты успешно отрендерены и скопированы")
        return True

def process_only_shuttles(base_directory):
    print_existing_renders()

    if not git_pull():
        print("Ошибка при выполнении git pull")
        return

    # Обработка только шаттлов
    shuttle_data = process_shuttles(base_directory)

    # Сохраняем данные шаттлов
    if not save_shuttles_json(shuttle_data, base_directory):
        print("Ошибка при сохранении shipyard_data.json")
        return

def process_shuttles(base_directory):
    shuttle_data = []
    shuttle_paths = [
        os.path.join(base_directory, 'Resources/Prototypes/_NF/Shipyard')
    ]

    # Список базовых файлов, которые нужно пропустить
    base_files = {'base.yml', 'BaseVessel.yml', 'BaseVesselAntag.yml'}

    print("\nОбработка шаттлов:")
    for base_path in shuttle_paths:
        if os.path.exists(base_path):
            print(f"✓ {base_path} (существует)")
            try:
                # Рекурсивно обходим все поддиректории
                for root, dirs, files in os.walk(base_path):
                    for file in files:
                        # Пропускаем базовые файлы
                        if file in base_files:
                            print(f"  Пропущен базовый файл: {file}")
                            continue

                        if file.endswith(('.yml', '.yaml')):
                            shuttle_path = os.path.join(root, file)
                            try:
                                with open(shuttle_path, 'r', encoding='utf-8') as f:
                                    data = yaml.safe_load(f)
                                    if isinstance(data, list):
                                        # Ищем элемент с type: vessel и проверяем, что это не базовый шаблон
                                        vessel_data = next((item for item in data
                                            if item.get('type') == 'vessel'
                                            and not item.get('id', '').startswith('Base')), None)

                                        if vessel_data:
                                            shuttle_info = {
                                                "id": vessel_data.get('id', ''),
                                                "name": vessel_data.get('name', ''),
                                                "description": vessel_data.get('description', ''),
                                                "category": vessel_data.get('category', 'Unknown'),
                                                "price": vessel_data.get('price', 0),
                                                "group": vessel_data.get('group', 'Shipyard'),
                                                "class": vessel_data.get('class', ['Civilian']),
                                                "engine": vessel_data.get('engine', ["Unknown"])
                                            }
                                            shuttle_data.append(shuttle_info)

                                            # Принудительно копируем рендеры для каждого шаттла
                                            print(f"\nКопирование рендеров для шаттла: {shuttle_info['name']}")
                                            if not copy_shuttle_renders(shuttle_info['id']):
                                                print(f"❌ Ошибка копирования рендеров для {shuttle_info['name']}")

                                print(f"  + {file} (обработан)")
                            except yaml.constructor.ConstructorError as e:
                                print(f"  ! Неподдерживаемый тег в файле {file}: {str(e)}")
                                continue
                            except Exception as e:
                                print(f"  ! Ошибка при обработке {file}: {str(e)}")
                                continue
            except Exception as e:
                print(f"  Ошибка при чтении директории {base_path}: {e}")
        else:
            print(f"✗ {base_path} (не найдена)")

    return shuttle_data

def save_shuttles_json(shuttle_data, base_directory):
    # Сохраняем локальную копию
    local_json_path = os.path.join(base_directory, 'storage/app/shuttles/shipyard_data.json')
    os.makedirs(os.path.dirname(local_json_path), exist_ok=True)

    with open(local_json_path, 'w', encoding='utf-8') as json_file:
        json.dump(shuttle_data, json_file, indent=4, ensure_ascii=False)
    print(f'Обновлен: {local_json_path}')

    # Сохраняем копию в веб-директории
    web_json_path = "/var/www/www-root/data/www/shipyard.webcodewizard.ru/storage/app/shuttles/shipyard_data.json"
    web_json_dir = os.path.dirname(web_json_path)

    try:
        os.makedirs(web_json_dir, exist_ok=True)
        with open(web_json_path, 'w', encoding='utf-8') as json_file:
            json.dump(shuttle_data, json_file, indent=4, ensure_ascii=False)
        print(f'Обновлен: {web_json_path}')
        return True
    except Exception as e:
        print(f"ОШИБКА при сохранении {web_json_path}: {e}")
        return False

def check_shuttle_needs_render(shuttle_id):
    # Проверяем несколько возможных путей для рендеров
    possible_paths = [
        Path(f"Resources/MapImages/Shuttles/{shuttle_id}"),
        Path(f"Resources/MapImages/{shuttle_id}"),
        Path(f"/var/www/www-root/data/www/shipyard.webcodewizard.ru/public/images/renders/{shuttle_id}")
    ]

    for path in possible_paths:
        if path.exists() and any(path.glob("*.png")):
            print(f"✓ Найдены существующие рендеры для шаттла {shuttle_id} в {path}")
            return False

    print(f"✗ Рендеры не найдены для шаттла {shuttle_id}. Проверены пути:")
    for path in possible_paths:
        print(f"  - {path}")
    return True

def copy_shuttle_renders(shuttle_id):
    print(f"\n=== Начало копирования рендеров шаттла {shuttle_id} ===")

    source_paths = [
        Path(f"Resources/MapImages/Shuttles/{shuttle_id}"),
        Path(f"Resources/MapImages/{shuttle_id}")
    ]
    dest_dir = Path("/var/www/www-root/data/www/shipyard.webcodewizard.ru/public/images/renders")

    print("\nПроверяемые исходные директории:")
    for source_path in source_paths:
        print(f"- {source_path} {'(существует)' if source_path.exists() else '(не существует)'}")

    print(f"\nЦелевая директория: {dest_dir}")
    print(f"Целевая директория существует: {dest_dir.exists()}")

    try:
        dest_dir.mkdir(parents=True, exist_ok=True)
        print("Целевая директория создана/проверена успешно")
        copied = False

        for source_dir in source_paths:
            if source_dir.exists():
                print(f"\nОбработка директории: {source_dir}")
                png_files = list(source_dir.glob("*.png"))
                print(f"Найдено PNG файлов: {len(png_files)}")

                for source_file in png_files:
                    try:
                        # Используем оригинальное имя файла
                        dest_file = dest_dir / source_file.name
                        print(f"\nКопирование:")
                        print(f"Из: {source_file}")
                        print(f"В: {dest_file}")

                        print(f"Права доступа к исходному файлу: {oct(source_file.stat().st_mode)[-3:]}")
                        if dest_file.exists():
                            print(f"Права доступа к существующему целевому файлу: {oct(dest_file.stat().st_mode)[-3:]}")

                        shutil.copy2(source_file, dest_file)
                        print(f"✅ Успешно скопировано: {source_file.name}")
                        copied = True
                    except Exception as e:
                        print(f"❌ Ошибка при копировании {source_file.name}: {str(e)}")
                        print(f"Тип ошибки: {type(e).__name__}")
            else:
                print(f"\nДиректория не существует: {source_dir}")

        if not copied:
            print(f"\n❌ Не найдены рендеры для копирования в путях: {[str(p) for p in source_paths]}")
            return False

        print(f"\n=== Завершено копирование рендеров шаттла {shuttle_id} ===")
        return True
    except Exception as e:
        print(f"❌ Критическая ошибка при копировании рендеров: {str(e)}")
        print(f"Тип ошибки: {type(e).__name__}")
        return False

def render_shuttle(shuttle_id, shuttle_path):
    print(f"\nНачинаем рендер шаттла {shuttle_id}")
    print(f"Путь к файлу: {shuttle_path}")

    try:
        with open(shuttle_path, 'r', encoding='utf-8') as f:
            data = yaml.safe_load(f)
            if isinstance(data, list):
                game_map = next((item for item in data if item.get('type') == 'gameMap'), None)
                if game_map and 'stations' in game_map:
                    station_name = next(iter(game_map['stations']))
                    print(f"Имя станции для рендера: {station_name}")

                    # Пробуем с оригинальным названием
                    command = f'dotnet run --project Content.MapRenderer {station_name}'
                    print(f"Выполняется команда: {command}")

                    if run_render_command(command):
                        print(f"✓ Рендер шаттла {shuttle_id} успешно завершен")
                        if copy_shuttle_renders(shuttle_id):
                            return True
                        return False

                    # Пробуем с маленькой буквы
                    station_name_lower = station_name.lower()
                    print(f"Пробуем рендер с маленькой буквы: {station_name_lower}")
                    command = f'dotnet run --project Content.MapRenderer {station_name_lower}'

                    if run_render_command(command):
                        print(f"✓ Рендер шаттла {shuttle_id} успешно завершен (с маленькой буквы)")
                        if copy_shuttle_renders(shuttle_id):
                            return True
                        return False

                    # Если оба варианта не сработали, пробуем retry_render
                    print(f"✗ Ошибка при рендере шаттла {shuttle_id}")
                    if retry_render(shuttle_id):
                        print(f"✓ Успешный повторный рендер шаттла {shuttle_id}")
                        if copy_shuttle_renders(shuttle_id):
                            return True
                    return False
                else:
                    print(f"✗ Не найдена секция gameMap или stations в файле {shuttle_path}")
                    return False
    except Exception as e:
        print(f"✗ Исключение при рендере шаттла {shuttle_id}: {str(e)}")
        return False

def run_render_command(command):
    process = subprocess.Popen(
        command,
        shell=True,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        text=True
    )

    while True:
        output = process.stdout.readline()
        error = process.stderr.readline()

        if output:
            print(f"  [OUT] {output.strip()}")
        if error:
            print(f"  [ERR] {error.strip()}")

        if output == '' and error == '' and process.poll() is not None:
            break

    return process.poll() == 0

if __name__ == "__main__":
    base_directory = os.path.dirname(os.path.abspath(__file__))
    process_only_shuttles(base_directory)
