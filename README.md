# DoctorDanila/ScriptDoc

Пакет для автоматической генерации веб-интерфейса документации на основе Markdown-файлов. Подходит для любых PHP-проектов: от простых скриптов до фреймворков Yii и Laravel.

## Возможности

- Рендеринг `.md` файлов из папки `docs/` в виде веб-страниц.
- Автоматическое построение дерева навигации с учётом вложенных директорий.
- Отображение корневых файлов проекта: `README.md`, `LICENSE`, `CONTRIBUTING.md` (поиск без учёта регистра).
- Встраивание ссылки на Swagger-документацию (опционально).
- Поддержка GitHub Flavored Markdown (через `cebe/markdown`).
- Простая интеграция через один класс-контроллер.

## Где размещён

- Пакет доступен на Packagist: `doctordanila/script-doc`
- Исходный код: [https://github.com/DoctorDanila/script-doc](https://github.com/DoctorDanila/script-doc)

## Установка

Установите пакет через Composer:

```bash
composer require doctordanila/script-doc
```

## Как подключить в проект

### Обычный PHP-проект

1. Подключите автозагрузку Composer и настройте контроллер документации.
2. Вызовите `handleRequest()` в точке входа (например, `index.php`).
3. Настройте веб-сервер так, чтобы все запросы к `/docs/*` направлялись на этот скрипт.

Пример `public/index.php`:

```php
<?php
require __DIR__ . '/../vendor/autoload.php';

use DoctorDanila\ScriptDoc\Include\Controller;

$docController = (new Controller())
    ->setProjectName('Мой проект')
    ->setDocsDir(__DIR__ . '/../docs')      // путь к папке с .md файлами
    ->setRoutePrefix('/docs')               // URL-префикс
    ->setSwaggerPath('/api/docs');          // ссылка на Swagger (опционально)

$docController->handleRequest();
```

Для локального тестирования используйте встроенный сервер PHP:

```bash
php -S localhost:8000 -t public/
```

Теперь документация доступна по адресу `http://localhost:8000/docs`.

### Подключение к Yii (Yii2)

Создайте модуль для документации:

1. Файл `modules/docs/Module.php`:

```php
<?php
namespace app\modules\docs;

use yii\base\Module as BaseModule;
use DoctorDanila\ScriptDoc\Include\Controller;

class Module extends BaseModule
{
    public $controllerNamespace = 'app\modules\docs\controllers';
    public $defaultRoute = 'default/index';

    public function init()
    {
        parent::init();
        // Дополнительная настройка модуля
    }
}
```

2. Файл `modules/docs/controllers/DefaultController.php`:

```php
<?php
namespace app\modules\docs\controllers;

use yii\web\Controller as YiiController;
use DoctorDanila\ScriptDoc\Include\Controller as DocController;

class DefaultController extends YiiController
{
    public function actionIndex()
    {
        $doc = new DocController();
        $doc->setProjectName('Мой проект')
            ->setDocsDir(\Yii::getAlias('@app/docs'))
            ->setRoutePrefix('/docs')
            ->setSwaggerPath('/api/docs');

        $doc->handleRequest(); // сам завершит выполнение
    }
}
```

3. Зарегистрируйте модуль в конфигурации `config/web.php`:

```php
'modules' => [
    'docs' => [
        'class' => 'app\modules\docs\Module',
    ],
],
```

Теперь документация будет доступна по URL `/docs`.

### Подключение к Laravel

1. Создайте сервис-провайдер и маршрут.
2. В файле `app/Providers/DocServiceProvider.php`:

```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DoctorDanila\ScriptDoc\Include\Controller;

class DocServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Controller::class, function ($app) {
            return (new Controller())
                ->setProjectName(config('app.name'))
                ->setDocsDir(base_path('docs'))
                ->setRoutePrefix('/docs')
                ->setSwaggerPath('/api/docs');
        });
    }

    public function boot()
    {
        $controller = $this->app->make(Controller::class);
        $controller->handleRequest();
    }
}
```

3. Зарегистрируйте провайдер в `config/app.php` (секция `providers`):

```php
App\Providers\DocServiceProvider::class,
```

4. Настройте веб-сервер (Nginx) так, чтобы запросы к `/docs/*` не обрабатывались Laravel-роутингом, а направлялись напрямую на точку входа, либо создайте простой роут в `routes/web.php`:

```php
Route::any('/docs/{any?}', function () {
    // Обработчик уже перехватит запрос через сервис-провайдер
})->where('any', '.*');
```

После этого документация станет доступна по `/docs`.

## Команда для просмотра документации

Если вы используете встроенный сервер PHP, запустите команду:

```bash
php -S localhost:8000 -t public/
```

Затем откройте `http://localhost:8000/docs`. Маршрут `/docs` будет автоматически перехвачен контроллером пакета.

## Настройка пакета при подключении

Класс `Controller` предоставляет цепочку методов для конфигурации:

| Метод               | Описание                                                     |
|---------------------|--------------------------------------------------------------|
| `setProjectName()`  | Название проекта (отображается в заголовке страницы)         |
| `setDocsDir()`      | Абсолютный путь к папке с Markdown-документацией             |
| `setRoutePrefix()`  | URL-префикс, по которому будет доступна документация (по умолчанию `/docs`) |
| `setProjectRootDir()` | Корневая директория проекта (по умолчанию – родительская от `docsDir`) |
| `setSwaggerPath()`  | Внешняя ссылка на Swagger-описание API (опционально)         |

Все методы возвращают текущий экземпляр `Controller`, позволяя строить цепочку вызовов.

## Известные проблемы

- **Чувствительность к регистру в URL.** Навигация и роутинг внутри `docs/` преобразуют пути к нижнему регистру, но имена файлов и папок в самой файловой системе должны соответствовать этому регистру (по возможности используйте имена в нижнем регистре).
- **Прямые ссылки** внутри документов могут не работать. В основном это зона роста для работы с корневыми файлами и ссылками с указанием расширения. Будет исправлено в ближайшем патче.
- **Отсутствие кеширования.** При каждом запросе файлы сканируются заново. При большом количестве документов это может снизить производительность. Рекомендуется кешировать результат на уровне веб-сервера.
- **Права доступа.** Убедитесь, что PHP имеет права на чтение папки `docs/` и корневых файлов (`README.md`, `LICENSE` и т.д.).
- **Вложенные директории без README.md.** Если папка не содержит ни одного `.md` файла и не имеет собственного `README.md`, она будет скрыта из навигации.
- **Зависимость от `cebe/markdown`.** Пакет использует библиотеку `cebe/markdown` для преобразования Markdown. Если в документации встречаются специфические расширения, не поддерживаемые этой библиотекой, рендеринг может отличаться.

## Рекомендации по процессу работы

1. **Структура документации.** Держите основные разделы в отдельных папках внутри `docs/` и обязательно добавляйте файл `README.md` для каждого раздела – он будет отображаться при переходе в соответствующую папку.
2. **Именование файлов.** Старайтесь придерживаться нижнего регистра и избегайте специальных символов в названиях, чтобы упростить навигацию.
3. **Интеграция с CI/CD.** Добавьте шаг в процесс сборки, который проверяет наличие и корректность Markdown-файлов (например, линтер Markdown).
4. **Безопасность.** Документация доступна публично. Не размещайте в папке `docs/` конфиденциальную информацию. При необходимости настройте ограничение доступа на уровне веб-сервера.
5. **Альтернативный веб-сервер.** Для production-окружения рекомендуется использовать Nginx или Apache с rewrite-правилами, направляющими запросы к `/docs/*` на ваш PHP-скрипт, минуя основной роутинг фреймворка, чтобы избежать накладных расходов.
