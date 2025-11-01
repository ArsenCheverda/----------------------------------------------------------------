<?php

// --- 1. Незмінний інтерфейс (Target) ---
// Цей інтерфейс нам дано і ми не можемо його змінювати.

interface Notification
{
    public function send(string title, string message);
}

// --- 2. Існуючий клас, що реалізує інтерфейс ---

class EmailNotification implements Notification
{
    private adminEmail;

    public function __construct(string adminEmail)
    {
        this->adminEmail = adminEmail;
    }

    public function send(string title, string message): void
    {
        // mail(this->adminEmail, title, message); // Імітація відправки
        echo "Sent email with title 'title' to '{this->adminEmail}' that says 'message'.\n";
    }
}

// --- 3. Сторонні сервіси з несумісними інтерфейсами (Adaptee) ---
// Це нові сервіси, які ми хочемо інтегрувати.

/**
 * Клас для роботи з API Slack.
 * Має власний метод sendOnSlack, який несумісний з Notification::send.
 */
class SlackApi
{
    // Методи цього класу не деталізуємо, згідно з умовою
    public function logIn(string login, string apiKey): void
    {
        // Імітація логіну
        echo "Logged in to Slack with login '{login}'.\n";
    }

    public function sendMessageToChat(string chatId, string message): void
    {
        // Імітація відправки повідомлення
        echo "Sent Slack message to chat '{chatId}': 'message'.\n";
    }
}

/**
 * Клас для роботи з SMS-шлюзом.
 * Має власний метод sendSms, який несумісний з Notification::send.
 */
class SmsService
{
    // Методи цього класу не деталізуємо, згідно з умовою
    public function sendSmsNotification(string phone, string sender, string message): void
    {
        // Імітація відправки SMS
        echo "Sent SMS from 'sender' to phone 'phone' that says 'message'.\n";
    }
}


// --- 4. Класи-адаптери (Adapter) ---
// Ці класи "адаптують" несумісні інтерфейси SlackApi та SmsService
// до нашого цільового інтерфейсу Notification.

/**
 * Адаптер для SlackApi.
 * Він реалізує інтерфейс Notification і "огортає" об'єкт SlackApi.
 */
class SlackNotificationAdapter implements Notification
{
    private slackApi;
    private login;
    private apiKey;
    private chatId;

    public function __construct(SlackApi slackApi, string login, string apiKey, string chatId)
    {
        this->slackApi = slackApi;
        this->login = login;
        this->apiKey = apiKey;
        this->chatId = chatId;
        
        // Можна виконати логін одразу при створенні адаптера
        this->slackApi->logIn(this->login, this->apiKey);
    }

    /**
     * Цей метод реалізує інтерфейс Notification.
     * Він перетворює виклик send() у виклик sendMessageToChat() об'єкта SlackApi.
     */
    public function send(string title, string message): void
    {
        // Форматуємо повідомлення для Slack
        slackMessage = "[title] message";
        this->slackApi->sendMessageToChat(this->chatId, slackMessage);
    }
}

/**
 * Адаптер для SmsService.
 * Він реалізує інтерфейс Notification і "огортає" об'єкт SmsService.
 */
class SmsNotificationAdapter implements Notification
{
    private smsService;
    private phone;
    private sender;

    public function __construct(SmsService smsService, string phone, string sender)
    {
        this->smsService = smsService;
        this->phone = phone;
        this->sender = sender;
    }

    /**
     * Цей метод реалізує інтерфейс Notification.
     * Він перетворює виклик send() у виклик sendSmsNotification() об'єкта SmsService.
     */
    public function send(string title, string message): void
    {
        // Форматуємо повідомлення для SMS (наприклад, ігноруємо заголовок або додаємо його)
        smsMessage = "title: message";
        this->smsService->sendSmsNotification(this->phone, this->sender, smsMessage);
    }
}


// --- 5. Клієнтський код (Приклад використання) ---

echo "--- Client Code Demonstration ---\n\n";

// A. Створюємо екземпляр існуючої системи
emailNotification = new EmailNotification('admin@example.com');

// B. Створюємо екземпляри нових сервісів (Adaptees)
slackApi = new SlackApi();
smsService = new SmsService();

// C. Створюємо екземпляри Адаптерів, "огортаючи" нові сервіси
// та надаючи необхідну конфігурацію
slackAdapter = new SlackNotificationAdapter(
    slackApi,
    'my_slack_login',
    'my_api_key_12345',
    '#general'
);

smsAdapter = new SmsNotificationAdapter(
    smsService,
    '+380991234567',
    'MySystem'
);

// D. Клієнтський код працює з усіма об'єктами через ЄДИНИЙ інтерфейс Notification
// Йому неважливо, що "під капотом" - email, Slack чи SMS.

/**
 * @param Notification notification
 * @param string title
 * @param string message
 */
function sendNotification(Notification notification, string title, string message)
{
    notification->send(title, message);
}

// Відправляємо сповіщення
echo "1. Sending Email:\n";
sendNotification(emailNotification, 'System Alert', 'Server is down!');


echo "2. Sending Slack Notification:\n";
sendNotification(slackAdapter, 'System Alert', 'Server is down!');


echo "3. Sending SMS Notification:\n";
sendNotification(smsAdapter, 'CRITICAL', 'Server is DOWN! Check immediately!');


?>
