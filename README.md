Компонент solo-assets
===========

Реализует публикацию JavaScript файлов с возможностью комбинирования и сжатия

Установка
=========

Установка через composer:

	"require": {
		"solo/assets": ">=1"
	}

Настройка
=========

Компонент имеет адаптер для подключения его в проект в виде ApplicationComponent

В файле common.php добавить

	"components" => array(
		"solo_assets" => array
        (
            "@class" => "Solo\\Web\\Assets\\SoloAdapter",
            "ttl" => 86400,
            "debug" => true,
            "async" => false,
            "outdir" => "/assets"
        ),
	)

и в секции настроек обработчика шаблонов нужно подключить расширение (например, Smarty функцию)

	"controller" => array(
		....
		"options" => array(
			....
			"plugins" => array('"Solo\\Web\\Assets\\Smarty\\Assets")
		)
	)

Опции
=====

 * files - строка, список файлов ресурсов через запятую
 * async - нужно ли добавлять атрибут async к сгенерированному тегу script (только в debug=false)
 * debug - режим отладки, если TRUE - подключает все файлы по отдельности, если FALSE - комбинирует все файлы в один. (TRUE по-умолчанию)
 * ttl - время в секундах, через которое происходит проверка файлов на изменение (если 0 - проверка происходит при каждом запросе)
 * outdir - путь к каталогу, в котором находятся скомпилированные файлы. Должен быть доступен как публичный каталог на сервере. По-умолчанию - /assets.
 * documentRootDir - путь к каталогу, который находится под контролем web-сервера. Каталог outdir определяется относительно него. По-умолчанию соответствует $_SERVER["DOCUMENT_ROOT"]
 Внимание! на каталог outdir должны быть разрешения на запись



Пример
======

Рекомендуется использовать Smarty функцию

index.html

	{assets files='/js/common.js,/js/another.js' ttl=10 outdir='/assetsDir' debug=true async=false}

Параметры debug, async, ttl и outdir необязательные, но если заданы, то имеют больший приоритет над настройками, определенными в
файле конфигурации.