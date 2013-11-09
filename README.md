API для работы с сервисом Яндекс.Фотки
======================================

На данный момент поддерживается только авторизация по Fimp-Token'у

```php
$api = new \Yandex\Fotki\Api($login);

// Загрузка сервисного документа, в котором ссылки на коллекции
// пункт необязательный, но в Яндексе написано, что ссылки могут
// когда-нибудь измениться
$api->loadServiceDocument()

// Аутентификация
// Принимает пароль от аккаунта или fimp-token
// Внимание! При получении Fimp-токена - Яндекс часто отвечает 502 ошибкой
try {
    $api->auth($passwordOrToken);
} catch(\Yandex\Fotki\Exception\ServerError $ex) {
    // Яндекс ответил 502. Повторите попытку снова. Как правило, раза с 5 удается получить токен
} catch(\Yandex\Fotki\Exception\Api\Auth $ex) {
    // Что-то с самой авторизацией (см. $ex->getMessage())
}

// Сохраните токен куда-нибудь. По документации - время жизни токена неограничено
$token = $api->getAuth()->getToken();

// Получение коллекции альбомов
$collection = $api->getAlbumsCollection()
    ->setLimit(5) // лимит на загрузку 5 альбомов
    ->load();
$collection->getList();

// Загрузка всей коллекции
$collection = $api->getAlbumsCollection()->loadAll();

// Постраничная загрузка коллекции
try {
    $collection = $api->getAlbumsCollection()->load()->next()->next()->next();
} catch(\Yandex\Fotki\Exception\Api\StopIteration $ex) {
    // Окончание постраничной навигации
}

// Список альбомов в коллекции
$albums = $collection->getList();

$album = null;
// Проходимся по альбомам коллекции (берем первый попавшийся)
foreach($albums as $id => $item) {
    $album = $item;
    break;
}

// Работа с альбомом - это работа с коллекцией фотографий
// и мета-информацией об альбоме.
// Загружаем все фотки альбома
$photos = $album->loadAll()->getList();
```

По всему коду прописаны php-doc комментарии, поэтому в IDE должны быть нормальные подсказки по методам у объектов.

Кэширование
-----------
Для объектов API реализован интерфейс Serializable, что позволит вам сохранять полученные объекты в текстовом представлении.

```php
$api = new \Yandex\Fotki\Api($login);
$api->auth($token);

// Загружаем все альбомы в коллекцию
$collection = $api->getAlbumsCollection()->loadAll();

// Сериализуем коллекцию (и можем сохранить в кэш, например)
echo serialize($collection);
```