<?php

/*
Інтерфейс Відвідувача оголошує набір методів відвідування для кожного 
конкретного класу елемента (Employee, Department, Company).
*/
interface Visitor
{
    public function visitEmployee(Employee $employee): void;
    public function visitDepartment(Department $department): void;
    public function visitCompany(Company $company): void;
}

/*
Інтерфейс Компонента (Element) оголошує метод accept, який приймає 
базовий інтерфейс відвідувача.
*/
interface Component
{
    public function accept(Visitor $visitor): void;
}

/*
Співробітник (Employee)
Згідно завдання: при створенні приймає назву позиції та розмір зарплати.
*/
class Employee implements Component
{
    public string $position;
    public float $salary;

    public function __construct(string $position, float $salary)
    {
        $this->position = $position;
        $this->salary = $salary;
    }

    public function accept(Visitor $visitor): void
    {
        // Співробітник просто передає себе відвідувачу
        $visitor->visitEmployee($this);
    }
}

/*
Департамент (Department)
Згідно завдання: при створенні приймає масив співробітників.
*/
class Department implements Component
{
    /* @var Employee[]*/
    public array $employees;
    
    // Додав назву для зручності читання звіту, хоча в суворій умові це не вимагалося,
    // але це корисно для логіки "конкретного департаменту".
    public string $name;

    public function __construct(string $name, array $employees)
    {
        $this->name = $name;
        $this->employees = $employees;
    }

    public function accept(Visitor $visitor): void
    {
        // Департамент передає себе відвідувачу.
        // Логіка обходу вкладених співробітників реалізована саме у Відвідувачі,
        // а не тут, що дозволяє змінювати алгоритм звіту не змінюючи цей клас.
        $visitor->visitDepartment($this);
    }
}

/*
Компанія (Company)
Згідно завдання: при створенні приймає масив департаментів.
*/
class Company implements Component
{
    /* @var Department[]*/
    public array $departments;

    public function __construct(array $departments)
    {
        $this->departments = $departments;
    }

    public function accept(Visitor $visitor): void
    {
        $visitor->visitCompany($this);
    }
}

/*
Конкретний Відвідувач: Зарплатна відомість (SalaryReportVisitor).
Реалізує логіку збору даних про зарплати.
*/
class SalaryReportVisitor implements Visitor
{
    private string $report = "";
    private float $totalSalary = 0.0;

    // Метод для отримання тексту звіту
    public function getReport(): string
    {
        return $this->report;
    }

    // Метод для очищення звіту (щоб використати той самий об'єкт відвідувача повторно)
    public function clear(): void
    {
        $this->report = "";
        $this->totalSalary = 0.0;
    }

    public function visitEmployee(Employee $employee): void
    {
        // Форматуємо рядок для одного співробітника
        $line = sprintf("   - Позиція: %s, Зарплата: %.2f грн\n", $employee->position, $employee->salary);
        $this->report .= $line;
        $this->totalSalary += $employee->salary;
    }

    public function visitDepartment(Department $department): void
    {
        $this->report .= "Департамент: " . $department->name . "\n";
        
        // Відвідувач сам вирішує, що треба пройтися по співробітниках цього департаменту
        foreach ($department->employees as $employee) {
            $employee->accept($this);
        }
        
        $this->report .= "\n";
    }

    public function visitCompany(Company $company): void
    {
        $this->report .= "=== ЗВІТ ПО КОМПАНІЇ ===\n\n";
        
        foreach ($company->departments as $department) {
            $department->accept($this);
        }
        
        $this->report .= "------------------------\n";
        $this->report .= sprintf("ЗАГАЛЬНА СУМА ЗАРПЛАТ ПО КОМПАНІЇ: %.2f грн\n", $this->totalSalary);
        $this->report .= "========================\n";
    }
}

// --- КЛІЄНТСЬКИЙ КОД ---

// 1. Створення співробітників
$emp1 = new Employee("Junior PHP Developer", 800.00);
$emp2 = new Employee("Senior PHP Developer", 3500.00);
$emp3 = new Employee("QA Engineer", 1200.00);
$emp4 = new Employee("Project Manager", 2500.00);
$emp5 = new Employee("HR Manager", 1500.00);
$emp6 = new Employee("Accountant", 1800.00);

// 2. Створення департаментів (приймають масив співробітників)
$itDepartment = new Department("IT Department", [$emp1, $emp2, $emp3]);
$managementDepartment = new Department("Management", [$emp4, $emp5]);
$financeDepartment = new Department("Finance", [$emp6]);

// 3. Створення компанії (приймає масив департаментів)
$company = new Company([$itDepartment, $managementDepartment, $financeDepartment]);

// 4. Ініціалізація відвідувача
$reportVisitor = new SalaryReportVisitor();

// --- СЦЕНАРІЙ 1: Звіт для всієї компанії ---
// Викликаємо accept у об'єкта Company
$company->accept($reportVisitor);
echo $reportVisitor->getReport();

echo "\n\n";

// --- СЦЕНАРІЙ 2: Звіт для окремого департаменту ---
// Вимога: "можливість отримання репорту ... для конкретного департаменту окремо"
$reportVisitor->clear(); // Очищаємо попередній стан відвідувача

// Викликаємо accept безпосередньо у об'єкта конкретного департаменту
echo "=== ЗВІТ ДЛЯ ОКРЕМОГО ДЕПАРТАМЕНТУ (IT) ===\n";
$itDepartment->accept($reportVisitor);
echo $reportVisitor->getReport();