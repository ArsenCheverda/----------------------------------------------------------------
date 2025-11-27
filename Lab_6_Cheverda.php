<?php

/*
Лабораторна робота №6
Тема: Патерн проектування Замісник (Proxy)
*/

// 1. Інтерфейс Downloader
// Оголошує спільні операції як для реального об'єкта, так і для замісника.
interface Downloader
{
    public function download(string $url): string;
}

// 2. Клас SimpleDownloader (Реальний Суб'єкт)
// Це клас, який виконує корисну, але "важку" роботу (наприклад, завантаження великих файлів).
class SimpleDownloader implements Downloader
{
    public function download(string $url): string
    {
        // Імітація повільного завантаження
        echo "SimpleDownloader: Завантаження файлу з інтернету ($url)...\n";
        
        // Повертаємо умовний вміст файлу
        return "Вміст файлу: " . $url;
    }
}

// 3. Клас CachedDownloader (Замісник / Proxy)
// Цей клас перехоплює виклики до реального об'єкта. Він перевіряє, чи є результат у кеші.
// Якщо є — повертає його, якщо ні — звертається до реального об'єкта і зберігає результат.
class CachedDownloader implements Downloader
{
    /*
    @var SimpleDownloader
    */
    private $downloader;

    /*
    @var array
    */
    private $cache = [];

    public function __construct(SimpleDownloader $downloader)
    {
        // Замісник повинен мати посилання на реальний об'єкт
        $this->downloader = $downloader;
    }

    public function download(string $url): string
    {
        if (isset($this->cache[$url])) {
            echo "CachedDownloader: Отримання даних із кешу для ($url).\n";
        } else {
            echo "CachedDownloader: Кеш пустий. Виклик реального завантажувача.\n";
            // Делегування роботи реальному об'єкту
            $result = $this->downloader->download($url);
            // Збереження результату в кеш
            $this->cache[$url] = $result;
        }

        return $this->cache[$url];
    }
}


// Користувач працює з об'єктами через інтерфейс Downloader. Йому не важливо, чи працює він з "чистим" завантажувачем, чи з тим, що має кеш.
function clientCode(Downloader $subject)
{
    // Перший запит (буде завантажено з "інтернету")
    echo $subject->download("http://example.com/video.mp4") . "\n\n";

    // Другий запит того ж файлу (має бути взято з кешу, якщо це Proxy)
    echo $subject->download("http://example.com/video.mp4") . "\n\n";

    // Третій запит іншого файлу
    echo $subject->download("http://example.com/image.jpg") . "\n\n";
}

// --- Демонстрація ---

echo "--- Тест 1: Робота без кешування (пряме використання SimpleDownloader) ---\n";
$realSubject = new SimpleDownloader();
clientCode($realSubject);

echo "----------------------------------------------------------------------\n";

echo "--- Тест 2: Робота з кешуванням (використання CachedDownloader) ---\n";
// Створюємо реальний об'єкт і огортаємо його в Proxy
$proxy = new CachedDownloader($realSubject);
clientCode($proxy);

?>