?php
session_start();
/*
 * STUD GRADE TRACK CALCULATOR
 * A Simple OOP version based on the original Python calculator.
 *
 * The Subject class stores one subject.
 * The StudentGwaCalculator class calculates totals, GWA, and grade identifiers.
 * The form below collects input and displays the result.
 */

class Subject
{
    // Declare each property's data type to help PHP and the editor understand it.
    public string $name;
    public float $grade;
    public int $units;

    public function __construct(string $name, float $grade, int $units)
    {
        $this->name = $name;
        $this->grade = $grade;
        $this->units = $units;
    }

    // A grade of 3.00 or lower is considered passed in the original Python program.
    public function getStatus(): string
    {
        return $this->grade <= 3.00 ? "Passed" : "Not Passed";
    }
}
