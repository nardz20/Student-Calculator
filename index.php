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

class StudentGwaCalculator
{
    public string $studentName;
    /** @var Subject[] */
    public array $subjects;

    public function __construct(string $studentName, array $subjects)
    {
        $this->studentName = $studentName;
        $this->subjects = $subjects;
    }

    // Calculate the sum of grade * units and the total number of units.
    public function calculateGWA(): array
    {
        $totalWeightedGrade = 0;
        $totalUnits = 0;

        foreach ($this->subjects as $subject) {
            $totalWeightedGrade += $subject->grade * $subject->units;
            $totalUnits += $subject->units;
        }

        $gwa = $totalUnits > 0 ? $totalWeightedGrade / $totalUnits : 0;

        return [
            "totalUnits" => $totalUnits,
            "gwa" => $gwa
        ];
    }

    // Lower numeric value means a higher grade: 1.00 is the highest.
    public function getHighestGrade(): float
    {
        $highest = $this->subjects[0]->grade;

        foreach ($this->subjects as $subject) {
            if ($subject->grade < $highest) {
                $highest = $subject->grade;
            }
        }

        return $highest;
    }

    // Higher numeric value means a lower grade: for example, 5.00 is the lowest.
    public function getLowestGrade(): float
    {
        $lowest = $this->subjects[0]->grade;

        foreach ($this->subjects as $subject) {
            if ($subject->grade > $lowest) {
                $lowest = $subject->grade;
            }
        }

        return $lowest;
    }

    // Mark each subject if its grade matches the highest or lowest value.
    public function getGradeIdentifier(Subject $subject): string
    {
        $isHighest = $subject->grade == $this->getHighestGrade();
        $isLowest = $subject->grade == $this->getLowestGrade();

        // Keep the default label when the same grade is both highest and lowest.
        if ($isHighest && $isLowest) {
            return "(Highest or Lowest Grade)";
        }

        if ($isHighest) {
            return "(Highest Grade)";
        }

        if ($isLowest) {
            return "(Lowest Grade)";
        }

        return "";
    }

    public function getGradeIdentifierClass(Subject $subject): string
    {
        $isHighest = $subject->grade == $this->getHighestGrade();
        $isLowest = $subject->grade == $this->getLowestGrade();

        if ($isHighest && !$isLowest) {
            return "grade-tag-high";
        }

        if ($isLowest && !$isHighest) {
            return "grade-tag-low";
        }

        // If the grade is both highest and lowest (e.g. one subject or all ties),
        // return no extra class so the existing default yellow label is preserved.
        return "";
    }
}

function textCharValue(string $value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Keep submitted form values so the user does not have to retype them.
$studentName = trim($_POST["student_name"] ?? "");
$numberOfSubjects = isset($_POST["number_of_subjects"]) ? (int)$_POST["number_of_subjects"] : 0;
$rows = $_POST["subjects"] ?? [];
$errors = [];
$calculator = null;
$result = null;

if ($numberOfSubjects < 0) {
    $numberOfSubjects = 0;
}
if ($numberOfSubjects > 100) {
    $numberOfSubjects = 100;
}
if (!is_array($rows) || count($rows) == 0) {
    $rows = [];
}
