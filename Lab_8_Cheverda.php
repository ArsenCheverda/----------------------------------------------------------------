<?php

/*
АБСТРАКТНИЙ КЛАС (Шаблон)
Визначає скелет алгоритму оновлення сутності через REST API.
*/
abstract class BaseRestHandler
{
    /*
    ШАБЛОННИЙ МЕТОД
    Визначає порядок виконання кроків.
    Цей метод не можна перевизначати (final), щоб не порушити алгоритм.
    */
    final public function handleUpdate($id, array $data)
    {
        echo "------ Початок обробки запиту для " . static::class . " ------\n";

        // Крок 1: Отримання об'єкта (імітація)
        $entity = $this->getEntity($id);
        if (!$entity) {
            return $this->formResponse(404, "Not Found");
        }

        // Крок 2: Валідація
        // Хук (Hook) для специфічної фільтрації даних перед валідацією (якщо потрібно)
        $data = $this->filterDataBeforeValidation($data);

        if ($this->validateData($data) === false) {
            // Хук: Дії при провалі валідації (специфічно для Товару)
            $this->onValidationFailure($entity, $data);
            return $this->formResponse(400, "Validation Error");
        }

        // Крок 3: Формування запиту на збереження
        // Хук: Дії безпосередньо перед збереженням
        $this->beforeSave($entity, $data);
        
        $this->saveEntity($entity, $data);

        // Крок 4: Формування відповіді
        return $this->formResponse(200, "OK", $entity);
    }

    // --- Абстрактні методи (обов'язкові для реалізації) ---

    protected abstract function getEntity($id);
    
    protected abstract function validateData(array $data): bool;
    
    protected abstract function saveEntity($entity, array $data);

    // --- Стандартна реалізація методів (можна перевизначити) ---

    /*
    Формування відповіді. 
    За замовчуванням повертає код і статус.
    */
    protected function formResponse($code, $status, $entity = null)
    {
        return json_encode([
            "status_code" => $code,
            "status_text" => $status
        ]);
    }

    // --- ХУКИ (HOOKS) ---
    // Це "порожні" методи, які підкласи можуть перевизначити за потреби.

    protected function onValidationFailure($entity, array $data)
    {
        // За замовчуванням нічого не робимо
    }

    protected function filterDataBeforeValidation(array $data): array
    {
        return $data;
    }

    protected function beforeSave($entity, array $data)
    {
        // За замовчуванням нічого не робимо
    }
}

/*
Реалізація для ТОВАРУ (Product)
Особливість: Сповіщення адміністратору при помилці валідації.
*/
class ProductHandler extends BaseRestHandler
{
    protected function getEntity($id)
    {
        return ["id" => $id, "name" => "Laptop", "price" => 1000];
    }

    protected function validateData(array $data): bool
    {
        // Імітуємо перевірку: ціна не може бути мінусовою
        if (isset($data['price']) && $data['price'] < 0) {
            return false;
        }
        return true;
    }

    protected function saveEntity($entity, array $data)
    {
        echo "[DB] Product updated successfully.\n";
    }

    // ПЕРЕВИЗНАЧЕННЯ ХУКА: Вимога з завдання про сповіщення адміна
    protected function onValidationFailure($entity, array $data)
    {
        echo "!!! ALERT: Надсилаємо повідомлення адміністратору у месенджер: 'Помилка валідації товару {$entity['name']}' !!!\n";
    }
}

/*
Реалізація для КОРИСТУВАЧА (User)
Особливість: Заборонено змінювати email, навіть якщо валідація дозволяє.
*/
class UserHandler extends BaseRestHandler
{
    protected function getEntity($id)
    {
        return ["id" => $id, "email" => "old@example.com", "name" => "John Doe"];
    }

    protected function validateData(array $data): bool
    {
        // Стандартна валідація каже, що email - це ок поле (згідно завдання)
        return true;
    }

    protected function saveEntity($entity, array $data)
    {
        // Тут ми виводимо дані, щоб переконатися, що email не змінився
        echo "[DB] User saved. Data to update: " . json_encode($data) . "\n";
    }

    // ПЕРЕВИЗНАЧЕННЯ ХУКА: Вимога "Заборонено змінювати значення у полі email"
    // Використовуємо хук перед валідацією або перед збереженням.
    // Тут краще використати filterDataBeforeValidation, щоб видалити поле до обробки.
    protected function filterDataBeforeValidation(array $data): array
    {
        if (isset($data['email'])) {
            echo "-> Hook: Спроба зміни email виявлена. Видаляємо email із даних для оновлення.\n";
            unset($data['email']);
        }
        return $data;
    }
}

/*
Реалізація для ЗАМОВЛЕННЯ (Order)
Особливість: У відповіді повертати повний JSON сутності, а не лише статус.
*/
class OrderHandler extends BaseRestHandler
{
    protected function getEntity($id)
    {
        return ["id" => $id, "items" => ["apple", "banana"], "total" => 50];
    }

    protected function validateData(array $data): bool
    {
        return true;
    }

    protected function saveEntity($entity, array $data)
    {
        echo "[DB] Order updated.\n";
    }

    // ПЕРЕВИЗНАЧЕННЯ МЕТОДУ: Вимога повертати JSON-подання сутності
    protected function formResponse($code, $status, $entity = null)
    {
        $response = [
            "status_code" => $code,
            "status_text" => $status,
            "order_details" => $entity // Додаємо саму сутність
        ];
        return json_encode($response, JSON_PRETTY_PRINT);
    }
}

// --- ТЕСТУВАННЯ (КЛІЄНТСЬКИЙ КОД) ---

// 1. Тест Товару (з помилкою валідації, щоб перевірити сповіщення адміна)
echo "\n=== Тест 1: Оновлення Товару (невалідна ціна) ===\n";
$productUpdater = new ProductHandler();
$result = $productUpdater->handleUpdate(101, ['price' => -500]);
echo "Response: " . $result . "\n";

// 2. Тест Користувача (спроба змінити email)
echo "\n=== Тест 2: Оновлення Користувача (спроба зміни email) ===\n";
$userUpdater = new UserHandler();
// Передаємо нове ім'я та новий email
$result = $userUpdater->handleUpdate(55, ['name' => 'Jane Doe', 'email' => 'new@hacker.com']);
echo "Response: " . $result . "\n";

// 3. Тест Замовлення (перевірка розширеної відповіді)
echo "\n=== Тест 3: Оновлення Замовлення ===\n";
$orderUpdater = new OrderHandler();
$result = $orderUpdater->handleUpdate(777, ['status' => 'shipped']);
echo "Response: " . $result . "\n";