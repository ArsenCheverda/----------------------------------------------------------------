<?php

/*
1. Інтерфейс Стратегії
Визначає загальний метод для всіх варіантів розрахунку вартості доставки.
*/
interface DeliveryStrategy
{
    /*
    Метод розрахунку вартості.
    Приймає суму замовлення (або дані про замовлення) та повертає вартість доставки.
    * @param float $orderAmount Сума замовлення
    @return float Вартість доставки
    */
    public function calculateCost(float $orderAmount): float;
}

/*
2. Конкретні Стратегії
Реалізують різні алгоритми розрахунку вартості.
*/

// Стратегія 1: Самовивіз
class PickupStrategy implements DeliveryStrategy
{
    public function calculateCost(float $orderAmount): float
    {
        // Логіка для самовивозу (зазвичай безкоштовно)
        // Повертаємо 0.0, як приклад
        return 0.0;
    }
}

// Стратегія 2: Доставка зовнішньою службою
class ExternalDeliveryStrategy implements DeliveryStrategy
{
    public function calculateCost(float $orderAmount): float
    {
        // Логіка розрахунку для сторонніх сервісів (наприклад, Glovo/Uber)
        // Припустимо, фіксована вартість або відсоток
        return 100.0; 
    }
}

// Стратегія 3: Доставка власною службою
class OwnDeliveryStrategy implements DeliveryStrategy
{
    public function calculateCost(float $orderAmount): float
    {
        // Логіка для власної служби (наприклад, дешевше, ніж зовнішня)
        return 50.0;
    }
}

/*
3. Контекст
Зберігає посилання на об'єкт Стратегії та делегує йому виконання розрахунку.
Клієнтський код працює саме з цим класом.
*/
class DeliveryContext
{
    /*
    @var DeliveryStrategy
    */
    private $strategy;

    /*
    Конструктор може приймати стратегію за замовчуванням.
    */
    public function __construct(DeliveryStrategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /*
    Метод для зміни стратегії "на льоту".
    */
    public function setStrategy(DeliveryStrategy $strategy): void
    {
        $this->strategy = $strategy;
    }

    /*
    Виконання стратегії.
    Контекст не знає деталей розрахунку, він просто викликає метод стратегії.
    */
    public function executeCalculation(float $orderAmount): float
    {
        return $this->strategy->calculateCost($orderAmount);
    }
}

/*
4. Клієнтський код
Демонстрація роботи.
*/

// Сума замовлення
$orderAmount = 500.00;

echo "Сума замовлення: {$orderAmount} грн\n\n";

// --- Варіант А: Самовивіз ---
$context = new DeliveryContext(new PickupStrategy());
echo "Стратегія: Самовивіз\n";
echo "Вартість доставки: " . $context->executeCalculation($orderAmount) . " грн\n";
echo "--------------------------\n";

// --- Варіант Б: Зовнішня служба ---
// Змінюємо стратегію через сетер
$context->setStrategy(new ExternalDeliveryStrategy());
echo "Стратегія: Зовнішня служба доставки\n";
echo "Вартість доставки: " . $context->executeCalculation($orderAmount) . " грн\n";
echo "--------------------------\n";

// --- Варіант В: Власна служба ---
$context->setStrategy(new OwnDeliveryStrategy());
echo "Стратегія: Власна служба доставки\n";
echo "Вартість доставки: " . $context->executeCalculation($orderAmount) . " грн\n";