<?php

/*
Інтерфейс Посередника описує метод для обміну інформацією між компонентами.
*/
interface Mediator
{
    public function notify(object $sender, string $event): void;
}

/*
Базовий Компонент забезпечує зберігання екземпляра посередника
всередині об'єктів компонентів.
*/
class Component
{
    protected ?Mediator $mediator = null;

    public function setMediator(Mediator $mediator): void
    {
        $this->mediator = $mediator;
    }

    // Метод для доступу до імені класу (для логування)
    public function getName(): string
    {
        return (new ReflectionClass($this))->getShortName();
    }
}

/*
Конкретні компоненти.
Вони не знають про існування інших компонентів, тільки про Посередника.
*/

// Компонент: Текстове поле (Input)
class TextBox extends Component
{
    private string $name;
    private bool $isVisible = true;
    private bool $isRequired = false;
    private string $value = '';

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function setVisible(bool $visible): void
    {
        $this->isVisible = $visible;
        echo "Поле [{$this->name}] тепер " . ($visible ? "ВИДИМЕ" : "ПРИХОВАНЕ") . ".\n";
    }

    public function setRequired(bool $required): void
    {
        $this->isRequired = $required;
        echo "Поле [{$this->name}] тепер " . ($required ? "ОБОВ'ЯЗКОВЕ" : "НЕОБОВ'ЯЗКОВЕ") . ".\n";
    }

    // Симуляція введення тексту користувачем
    public function type(string $text): void
    {
        $this->value = $text;
        // Сповіщаємо посередника, якщо це потрібно (у цьому завданні це не критично, але для прикладу)
        if ($this->mediator) {
            $this->mediator->notify($this, "type");
        }
    }
}

// Компонент: Чекбокс (Checkbox)
class Checkbox extends Component
{
    private string $label;
    private bool $checked = false;

    public function __construct(string $label)
    {
        $this->label = $label;
    }

    public function isChecked(): bool
    {
        return $this->checked;
    }

    // Основна дія - зміна стану
    public function check(): void
    {
        $this->checked = !$this->checked;
        echo "\n--> Користувач клікнув чекбокс: '{$this->label}'. Новий стан: " . ($this->checked ? "TRUE" : "FALSE") . "\n";
        
        if ($this->mediator) {
            $this->mediator->notify($this, "check");
        }
    }
}

// Компонент: Випадаючий список (Select / DatePicker)
class SelectBox extends Component
{
    private string $label;
    private array $options = [];
    private string $selectedOption = '';

    public function __construct(string $label, array $options = [])
    {
        $this->label = $label;
        $this->options = $options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
        echo "Оновлено список опцій для [{$this->label}]: " . implode(", ", $options) . ".\n";
    }

    public function select(string $option): void
    {
        $this->selectedOption = $option;
        echo "\n--> Користувач обрав у '{$this->label}': {$option}\n";
        
        if ($this->mediator) {
            $this->mediator->notify($this, "change");
        }
    }
    
    public function getValue(): string
    {
        return $this->selectedOption;
    }
    
    public function setEnabled(bool $enabled): void
    {
        echo "Елемент [{$this->label}] тепер " . ($enabled ? "АКТИВНИЙ" : "НЕАКТИВНИЙ") . ".\n";
    }
}

/*
Конкретний Посередник.
Клас керує формою замовлення квітів. Він знає про всі поля і керує їх станом.
*/
class FlowerOrderMediator implements Mediator
{
    // Оголошуємо всі компоненти, якими керує посередник
    public $datePicker;
    public $timePicker;
    public $recipientCheckbox; // "Отримувач інша особа"
    public $recipientName;
    public $recipientPhone;
    public $pickupCheckbox;    // "Самовивіз"
    public $addressField;

    public function __construct(
        SelectBox $datePicker,
        SelectBox $timePicker,
        Checkbox $recipientCheckbox,
        TextBox $recipientName,
        TextBox $recipientPhone,
        Checkbox $pickupCheckbox,
        TextBox $addressField
    ) {
        $this->datePicker = $datePicker;
        $this->datePicker->setMediator($this);

        $this->timePicker = $timePicker;
        $this->timePicker->setMediator($this);

        $this->recipientCheckbox = $recipientCheckbox;
        $this->recipientCheckbox->setMediator($this);

        $this->recipientName = $recipientName;
        $this->recipientName->setMediator($this);

        $this->recipientPhone = $recipientPhone;
        $this->recipientPhone->setMediator($this);

        $this->pickupCheckbox = $pickupCheckbox;
        $this->pickupCheckbox->setMediator($this);
        
        $this->addressField = $addressField;
        $this->addressField->setMediator($this);
    }

    /*
    Головний метод логіки. Отримує сповіщення і вирішує, що робити.
    */
    public function notify(object $sender, string $event): void
    {
        // 1. Логіка зміни дати доставки -> змінюється час
        if ($sender === $this->datePicker && $event === "change") {
            $selectedDate = $this->datePicker->getValue();
            echo "Посередник реагує на зміну дати: {$selectedDate}...\n";
            
            // Умовна логіка: у вихідні менше слотів
            if ($selectedDate === "Субота" || $selectedDate === "Неділя") {
                $this->timePicker->setOptions(["10:00 - 14:00"]);
            } else {
                $this->timePicker->setOptions(["09:00 - 12:00", "13:00 - 18:00", "19:00 - 21:00"]);
            }
        }

        // 2. Логіка чекбоксу "Отримувач інша особа"
        if ($sender === $this->recipientCheckbox && $event === "check") {
            echo "Посередник реагує на чекбокс 'Інший отримувач'...\n";
            
            if ($this->recipientCheckbox->isChecked()) {
                // Показуємо поля і робимо їх обов'язковими
                $this->recipientName->setVisible(true);
                $this->recipientName->setRequired(true);
                $this->recipientPhone->setVisible(true);
                $this->recipientPhone->setRequired(true);
            } else {
                // Ховаємо поля і знімаємо обов'язковість
                $this->recipientName->setVisible(false);
                $this->recipientName->setRequired(false);
                $this->recipientPhone->setVisible(false);
                $this->recipientPhone->setRequired(false);
            }
        }

        // 3. Логіка "Самовивіз"
        if ($sender === $this->pickupCheckbox && $event === "check") {
            echo "Посередник реагує на чекбокс 'Самовивіз'...\n";
            
            $isPickup = $this->pickupCheckbox->isChecked();

            if ($isPickup) {
                // Вимикаємо всі поля доставки
                $this->datePicker->setEnabled(false);
                $this->timePicker->setEnabled(false);
                $this->addressField->setVisible(false);
                
                // Якщо самовивіз, то логічно вимкнути й "іншого отримувача" (за бажанням),
                // або просто деактивувати поля доставки.
                echo "-> Доставка вимкнена, клієнт забере букет сам.\n";
            } else {
                // Вмикаємо назад
                $this->datePicker->setEnabled(true);
                $this->timePicker->setEnabled(true);
                $this->addressField->setVisible(true);
                echo "-> Доставка увімкнена.\n";
            }
        }
    }
}

// --- КЛІЄНТСЬКИЙ КОД (Симуляція роботи форми) ---

echo "=== Ініціалізація форми замовлення квітів ===\n";

// 1. Створюємо компоненти
$dateSelect = new SelectBox("Дата доставки", ["Понеділок", "Вівторок", "Субота"]);
$timeSelect = new SelectBox("Час доставки", []);
$otherPersonChk = new Checkbox("Отримувач інша особа");
$nameInput = new TextBox("Ім'я отримувача");
$phoneInput = new TextBox("Телефон отримувача");
$pickupChk = new Checkbox("Самовивіз з магазину");
$addressInput = new TextBox("Адреса доставки");

// 2. Створюємо посередника і зв'язуємо його з компонентами
$mediator = new FlowerOrderMediator(
    $dateSelect,
    $timeSelect,
    $otherPersonChk,
    $nameInput,
    $phoneInput,
    $pickupChk,
    $addressInput
);

// --- СЦЕНАРІЙ 1: Вибір дати впливає на час ---
$dateSelect->select("Субота");
// Очікуваний результат: час оновиться на скорочений графік

$dateSelect->select("Вівторок");
// Очікуваний результат: повний список годин

// --- СЦЕНАРІЙ 2: Отримувач інша особа ---
$otherPersonChk->check(); 
// Очікуваний результат: поля "Ім'я" та "Телефон" стають Visible = true та Required = true

$otherPersonChk->check(); 
// Очікуваний результат: поля ховаються (чекбокс знято)

// --- СЦЕНАРІЙ 3: Самовивіз ---
$pickupChk->check();
// Очікуваний результат: поля доставки (дата, час, адреса) стають неактивними/прихованими