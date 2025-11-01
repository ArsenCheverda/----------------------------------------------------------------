<?php

/*
 Інтерфейс StorageInterface.
 Визначає загальні методи, які повинна мати кожна реалізація сховища.
*/
interface StorageInterface
{
    
    //Зберігає файл у сховищі.
    @param string $path //Шлях для збереження файлу.
    @param string $content //Вміст файлу.
    @return bool //Повертає true у разі успіху.

    public function putFile(string $path, string $content): bool;

    // Отримує файл зі сховища.
    @param string $path //Шлях до файлу.
    @return string|null //Повертає вміст файлу або null, якщо файл не знайдено.

    public function getFile(string $path): ?string;

    
    //Видаляє файл зі сховища.
    @param string $path //Шлях до файлу.
    @return bool //Повертає true у разі успіху.

    public function deleteFile(string $path): bool;
}


//Реалізація сховища для локального диска системи.
class LocalStorage implements StorageInterface
{
    
    //Конструктор та властивості ( $rootDirectory).
    private function __construct() { }
    

    public function putFile(string $path, string $content): bool
    {
        // логіка збереження файлу на диск 
        return true;
    }

    public function getFile(string $path): ?string
    {
        // логіка читання файлу з диска
        return "Контент файлу з локального диску";
    }

    public function deleteFile(string $path): bool
    {
        // логіка видалення файлу з диска
        return true;
    }
}


//Реалізація сховища для Amazon S3.
class S3Storage implements StorageInterface
{
    /*
    Тут знаходяться параметри, $bucket, $apiKey 
    private function __construct(string $apiKey, string $bucket) { ... }
    */

    public function putFile(string $path, string $content): bool
    {
        // логіка завантаження файлу в S3
        return true;
    }

    public function getFile(string $path): ?string
    {
        // логіка отримання файлу з S3
        return "Контент файлу з S3";
    }

    public function deleteFile(string $path): bool
    {
        // логіка видалення файлу з S3
        return true;
    }
}


/*
Цей клас реалізований за патерном "Одинак".
Він надає єдину глобальну точку доступу для отримання
екземплярів необхідних сховищ.
*/
class StorageManager
{
   
    @var ?StorageManager //Зберігає єдиний екземпляр цього класу.

    private static ?StorageManager $instance = null;

   
    @var StorageInterface[] //Масив для зберігання вже створених екземплярів сховищ щоб не створювати їх повторно.
    //Ключ – назва типу ('local', 's3').

    private array $storageInstances = [];

    /*
    Конструктор оголошено приватним.
    Це забороняє створювати екземпляри класу ззовні
    через оператор 'new'.
    */
    private function __construct()
    {
        // Тут знаходиться початкова ініціалізація.
    }

    
    //Забороняться клонування об'єкта.
    private function __clone()
    {
    }

    
    //Забороняється десеріалізацію.
    public function __wakeup()
    {
        throw new \Exception("Не можливо серелізувати Одинака.");
    }

    
    /*
    Головний статичний метод "Одинака".
    Він надає глобальну точку доступу до єдиного екземпляра StorageManager.
    */
   @return StorageManager

    public static function getInstance(): StorageManager
    {
        if (self::$instance === null) {
                self::$instance = new StorageManager();
        }
        return self::$instance;
    }

    /*
    Метод для отримання конкретного екземпляра сховища за типом.
    Тип сховища буде братися з налаштувань користувача.
    */
    @param string $storageType "local", "s3", ...
    @return StorageInterface
    @throws \Exception // Якщо тип сховища не підтримується.

    public function getStorage(string $storageType): StorageInterface
    {
        // Якщо екземпляр такого сховища вже був створений, просто повертаємо його з нашого реєстру.
        if (isset($this->storageInstances[$storageType])) {
            return $this->storageInstances[$storageType];
        }

        // Якщо екземпляра ще немає, ми його створюємо.
        $storage = match ($storageType) {
            'local' => new LocalStorage(),
            's3'    => new S3Storage(),
            // Сюди додаємо нові типи у майбутньому
            default => throw new \Exception("Невідомий тип сховища: $storageType"),
        };

        // Зберігаємо створений екземпляр у "реєстрі" для майбутніх запитів...
        $this->storageInstances[$storageType] = $storage;

        //і повертаємо його.
        return $storage;
    }
}



// Імітація класу користувача, який має налаштування сховища.
class User
{
    private string $username;
    private string $storagePreference; // 'local' або 's3'

    public function __construct(string $username, string $storagePreference)
    {
        $this->username = $username;
        $this->storagePreference = $storagePreference;
    }

    public function getStoragePreference(): string
    {
        return $this->storagePreference;
    }
}


//Головний клас програми, що демонструє рішення.

class Application
{
    public function main()
    {
        echo "Запуск системи управління файлами...\n";

        // Створюємо двох користувачів з різними налаштуваннями
        $user1 = new User("1", "local");
        $user2 = new User("2", "s3");
        $user3 = new User("3", "local");

        // Усі частини програми отримують доступ до одного й того ж менеджера сховищ через статичний метод getInstance().
        $manager = StorageManager::getInstance();
        
        // користувач 1 зберігає файл.
        // Менеджер дивиться на налаштування ("local") і видає йому екземпляр LocalStorage.
        $storage1 = $manager->getStorage($user1->getStoragePreference());
        $storage1->putFile("/1/cv.pdf", "...");
        echo "користувач1 зберег файл у " . get_class($storage1) . "\n";

        // користувач 2 зберігає файл.
        // Менеджер дивиться на його налаштування ("s3") і видає йому екземпляр S3Storage.
        $storage2 = $manager->getStorage($user2->getStoragePreference());
        $storage2->putFile("/2/avatar.png", "...");
        echo "користувач2 зберіг файл у " . get_class($storage2) . "\n";

        // користувач 3 хоче отримати файл.
        // Менеджер дивиться на його налаштування ("local") і видає йому тий же екземпляр LocalStorage, що і 1.
        $storage3 = $manager->getStorage($user3->getStoragePreference());
        echo "користувач3 отримав доступ до " . get_class($storage3) . "\n";

        // Демонстрація роботи Одинака
        $manager2 = StorageManager::getInstance();
        if ($manager === $manager2) {
            echo "SUCCESS: manager та manager2 - це один об'єкт.\n";
        }
        
        if ($storage1 === $storage3) {
            echo "SUCCESS: storage1 та storage3 - це один об'єкт.\n";
        }
        
        if ($storage1 !== $storage2) {
             echo "SUCCESS: storage1 та storage2 - це різні об'єкти.\n";
        }
    }
}

// Запуск
$app = new Application();
$app->main();
