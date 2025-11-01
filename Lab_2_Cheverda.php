<?php

/*
 Інтерфейс Продукту
 Оголошує методи, які мають реалізувати всі конкретні конектори до соціальних мереж.
 */
interface SocialNetworkConnector
{
     //Метод для автентифікації в соціальній мережі.
     @return void
     public function logIn(): void;

     //Метод для публікації повідомлення.
     @param string $message //Текст повідомлення.
     @return void
     public function postMessage(string $message): void;
}

/*
 Продукт 1
 Реалізує підключення та публікацію для Facebook.
 Використовує login та password для автентифікації.
*/
class FacebookConnector implements SocialNetworkConnector
{
     private string $login;
     private string $password;


     @param string $login
     @param string $password
     public function __construct(string $login, string $password)
     {
          $this->login = $login;
          $this->password = $password;
          echo "FacebookConnector: Створено з логіном {$this->login}\n";
     }

     {@inheritdoc}
     // Логіка підключення до Facebook API
     public function logIn(): void
     {
          echo "FacebookConnector: Вхід з логіном {$this->login}...\n";
          // Логіка автентифікації
          echo "FacebookConnector: Успішний вхід.\n";
     }

     {@inheritdoc}
     // Логіка відправки повідомлення
     public function postMessage(string $message): void
     {
          echo "FacebookConnector: Публікація повідомлення: '{$message}'\n";
     }
}

/*
Продукт 2
Реалізує підключення та публікацію для LinkedIn.
Використовує email та password для аутентифікації.
*/
class LinkedInConnector implements SocialNetworkConnector
{
    private string $email;
    private string $password;

    @param string $email
    @param string $password
    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
        echo "LinkedInConnector: Створено з email {$this->email}\n";
     }

    {@inheritdoc}
    // Догіка підключення до LinkedIn API
     public function logIn(): void
     {
        echo "LinkedInConnector: Вхід з email {$this->email}...\n";
        // Логіка аутентифікації
        echo "LinkedInConnector: Успішний вхід.\n";
     }

    {@inheritdoc}
    // Логіка відправки повідомлення
     public function postMessage(string $message): void
     {
          echo "LinkedInConnector: Публікація повідомлення: '{$message}'\n";
     }
}

/*
Створювач
Оголошує фабричний метод, який має повертати об'єкт Продукту.
Також містить основну бізнес-логіку, яка залежить від Продукту.
*/
abstract class SocialNetworkPoster
{
    /*
    Фабричний метод
    Підкласи реалізують цей метод, щоб створити екземпляр конектора.
    */
    @return SocialNetworkConnector
    abstract protected function getSocialNetwork(): SocialNetworkConnector;

    // Основна бізнес-логіка, яка використовує Продукт, створений фабричним методом.
    @param string $message
    @return void
    public function post(string $message): void
    {
          // Отримується продукт за допомогою методу
        $network = $this->getSocialNetwork();

          // Використовується продукт
         $network->logIn();
         $network->postMessage($message);

          echo "SocialNetworkPoster: Повідомлення успішно опубліковано.\n";
     }
}

/*
Створювач 1
Реалізує фабричний метод для створення FacebookConnector.
*/
class FacebookPoster extends SocialNetworkPoster
{
     private string $login;
     private string $password;

     @param string $login
     @param string $password
     public function __construct(string $login, string $password)
     {
          $this->login = $login;
          $this->password = $password;
          echo "FacebookPoster: Створено.\n";
     }

     {@inheritdoc}
     // Реалізація фабричного методу.
     @return SocialNetworkConnector
     protected function getSocialNetwork(): SocialNetworkConnector
     {
          return new FacebookConnector($this->login, $this->password);
     }
}

/*
Створювач 2
Реалізує фабричний метод для створення LinkedInConnector.
*/
class LinkedInPoster extends SocialNetworkPoster
{
     private string $email;
     private string $password;

     @param string $email
     @param string $password
     public function __construct(string $email, string $password)
     {
          $this->email = $email;
          $this->password = $password;
          echo "LinkedInPoster: Створено.\n";
     }

     {@inheritdoc}
     //Реалізація фабричного методу.
     @return SocialNetworkConnector
     protected function getSocialNetwork(): SocialNetworkConnector
     {
          return new LinkedInConnector($this->email, $this->password);
     }
}

/*
Клієнтський код
Працює з екземпляром Створювача, через його абстрактний інтерфейс.
 */
@param SocialNetworkPoster $poster
@param string $message
@return void
function clientCode(SocialNetworkPoster $poster, string $message): void
{
     echo "\nКлієнт: Я не знаю клас створювача, можу опублікувати повідомлення.\n";
     $poster->post($message);
}

//Приклад публікації в обох соціальних мережах

echo "Запуск публікації у Facebook\n";
$facebookPoster = new FacebookPoster("my_fb_login", "54312");
clientCode($facebookPoster, "якесь повідомлення");

echo "\nЗапуск публікації у LinkedIn\n";
$linkedInPoster = new LinkedInPoster("my.email@gmail.com", "12345");
clientCode($linkedInPoster, "якесь повідомлення");

?>

