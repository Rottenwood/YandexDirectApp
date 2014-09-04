Simple Symfony2 Yandex.Direct App
=================================
Strategy Manager for Yandex.Direct API

Приложение для автоматического управления стратегиями Яндекс.Директа через его API

Основные файлы
==============
Конфигурационный файл: https://github.com/Rottenwood/YandexDirectApp/blob/master/src/Petr/DirectApiBundle/Resources/config/yandexDirect.yml

Основной контроллер: https://github.com/Rottenwood/YandexDirectApp/blob/master/src/Petr/DirectApiBundle/Controller/DefaultController.php

Методы сервиса: https://github.com/Rottenwood/YandexDirectApp/blob/master/src/Petr/DirectApiBundle/Service/DirectService.php

Сущности для базы данных и ORM: https://github.com/Rottenwood/YandexDirectApp/tree/master/src/Petr/DirectApiBundle/Entity

Как пользоваться
================
* склонировать репозиторий
~~~
git clone https://github.com/Rottenwood/YandexDirectApp.git
~~~
* установить зависимости, указав реквизиты для доступа к БД
~~~
php composer.phar install
~~~
* разметить схему базы данных
~~~
app/console doctrine:schema:update --force
~~~
