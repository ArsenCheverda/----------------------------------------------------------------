<?php

// Клас, що представляє дані про товар.

class Product
{
    public string $id;
    public string $name;
    public string $description;
    public string $imageUrl;

    public function __construct(string $id, string $name, string $description, string $imageUrl)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
    }
}

/*
Інтерфейс Реалізації
Визначає методи для конкретних типів подання.
Абстракція буде використовувати ці методи.
*/
interface Renderer
{
    //Метод для рендерингу простої сторінки.
    @param string $title
    @param string $content
    @return string

    public function renderSimplePage(string $title, string $content): string;

    //Метод для рендерингу сторінки товару.
    @param Product $product
    @return string

    public function renderProductPage(Product $product): string;
}

// Класи, що реалізують інтерфейс Renderer для конкретних форматів.

//Подання у форматі HTML.
class HTMLRenderer implements Renderer
{
    public function renderSimplePage(string $title, string $content): string
    {
        // Повертає рядок у форматі HTML
        return "<!DOCTYPE html>\n" .
               "<html>\n" .
               "<head><title>" . htmlspecialchars($title) . "</title></head>\n" .
               "<body>\n" .
               "<h1>" . htmlspecialchars($title) . "</h1>\n" .
               "<p>" . htmlspecialchars($content) . "</p>\n" .
               "</body>\n" .
               "</html>";
    }

    public function renderProductPage(Product $product): string
    {
        // Повертає рядок у форматі HTML
        return "<!DOCTYPE html>\n" .
               "<html>\n" .
               "<head><title>" . htmlspecialchars($product->name) . "</title></head>\n" .
               "<body>\n" .
               "<div class='product' data-id='" . htmlspecialchars($product->id) . "'>\n" .
               "<h1>" . htmlspecialchars($product->name) . "</h1>\n" .
               "<img src='" . htmlspecialchars($product->imageUrl) . "' alt='" . htmlspecialchars($product->name) . "' />\n" .
               "<p>" . htmlspecialchars($product->description) . "</p>\n" .
               "</div>\n" .
               "</body>\n" .
               "</html>";
    }
}

// Подання у форматі JSON.
class JsonRenderer implements Renderer
{
    public function renderSimplePage(string $title, string $content): string
    {
        // Повертає рядок у форматі JSON
        $data = [
            'page' => [
                'type' => 'simple',
                'title' => $title,
                'content' => $content,
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public function renderProductPage(Product $product): string
    {
        // Повертає рядок у форматі JSON
        $data = [
            'page' => [
                'type' => 'product',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'image' => $product->imageUrl,
                ]
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}

// Подання у форматі XML.
class XmlRenderer implements Renderer
{
    public function renderSimplePage(string $title, string $content): string
    {
        // Повертає рядок у форматі XML
        return "<?xml version='1.0' encoding='UTF-8'?>\n" .
               "<page type='simple'>\n" .
               "  <title>" . htmlspecialchars($title) . "</title>\n" .
               "  <content>" . htmlspecialchars($content) . "</content>\n" .
               "</page>";
    }

    public function renderProductPage(Product $product): string
    {
        // Повертає рядок у форматі XML
        return "<?xml version='1.0' encoding='UTF-8'?>\n" .
               "<page type='product'>\n" .
               "  <product id='" . htmlspecialchars($product->id) . "'>\n" .
               "    <name>" . htmlspecialchars($product->name) . "</name>\n" .
               "    <description>" . htmlspecialchars($product->description) . "</description>\n" .
               "    <image>" . htmlspecialchars($product->imageUrl) . "</image>\n" .
               "  </product>\n" .
               "</page>";
    }
}

/*
Базовий клас абстракції.
Містить посилання на об'єкт реалізації.
Делегує виконання роботи об'єкту реалізації.*/
abstract class Page
{

    @var Renderer // Посилання на об'єкт реалізації (Міст).

    protected Renderer $renderer;

    // Конструктор приймає об'єкт реалізації.
    @param Renderer $renderer

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    // Метод для зміни реалізації.
    @param Renderer $renderer

    public function setRenderer(Renderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    // Абстрактний метод, який будуть реалізовувати уточнені абстракції.
    @return string

    abstract public function view(): string;
}

// Розширення базової "Абстракції" для конкретних типів сторінок.

// Проста сторінка.
class SimplePage extends Page
{
    protected string $title;
    protected string $content;

    public function __construct(Renderer $renderer, string $title, string $content)
    {
        parent::__construct($renderer);
        $this->title = $title;
        $this->content = $content;
    }

    /*
    Реалізація методу view.
    Делегує роботу конкретному рендереру.
     */
    public function view(): string
    {
        return $this->renderer->renderSimplePage($this->title, $this->content);
    }
}

// Сторінка товару.
class ProductPage extends Page
{
    protected Product $product;

    public function __construct(Renderer $renderer, Product $product)
    {
        parent::__construct($renderer);
        $this->product = $product;
    }

    /*
    Реалізація методу view.
    Делегує роботу конкретному рендереру.
     */
    public function view(): string
    {
        return $this->renderer->renderProductPage($this->product);
    }
}

// Приклад використання.

// Створюємо об'єкт товару
$product = new Product(
    'p123',
    'Ноутбук "1"',
    'Потужний ноутбук для роботи та розваг.',
    '/images/laptop.jpg'
);

// Створюємо об'єкти рендерерів
$htmlRenderer = new HTMLRenderer();
$jsonRenderer = new JsonRenderer();
$xmlRenderer = new XmlRenderer();

// Створюємо просту сторінку з HTML рендерером
$simplePage = new SimplePage($htmlRenderer, 'Про нас', 'Це сторінка про нашу компанію.');

// HTML Подання
echo $simplePage->view() . "\n\n";

// Міняємо рендерер на JSON
$simplePage->setRenderer($jsonRenderer);
// JSON Подання
echo $simplePage->view() . "\n\n";

// Міняємо рендерер на XML
$simplePage->setRenderer($xmlRenderer);
// XML Подання
echo $simplePage->view() . "\n\n";


// Створюємо сторінку товару з JSON рендерером
$productPage = new ProductPage($jsonRenderer, $product);

// JSON Подання
echo $productPage->view() . "\n\n";

// Міняємо рендерер на HTML
$productPage->setRenderer($htmlRenderer);
// HTML Подання
echo $productPage->view() . "\n\n";

// Міняємо рендерер на XML
$productPage->setRenderer($xmlRenderer);
// XML Подання
echo $productPage->view() . "\n\n";

?>
