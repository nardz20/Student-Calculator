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

// Calculate only when the Calculate GWA button is clicked.
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["calculate"])) {
    if ($studentName == "") {
        $errors[] = "Please enter the student name.";
    }

    if (count($rows) == 0) {
        $errors[] = "Please add at least one subject.";
    }

    $subjects = [];

    if (count($errors) == 0) {
        foreach ($rows as $row) {
            $name = trim($row["name"] ?? "");
            $gradeText = trim($row["grade"] ?? "");
            $unitsText = trim($row["units"] ?? "");

            // Check that fields are filled before converting them to numbers.
            if ($name == "" || $gradeText == "" || $unitsText == "" ||
                !is_numeric($gradeText) || !is_numeric($unitsText)) {
                $errors[] = "Enter a subject name, a grade from 1 to 5, and positive whole-number units.";
                break;
            }

            $grade = (float)$gradeText;
            $units = filter_var($unitsText, FILTER_VALIDATE_INT);

            if ($grade < 1 || $grade > 5 || $units === false || $units < 1) {
                $errors[] = "Enter a subject name, a grade from 1 to 5, and positive whole-number units.";
                break;
            }

            // Turn the input row into a Subject object.
            $subjects[] = new Subject($name, $grade, $units);
        }
    }
                
if (count($errors) == 0) {
        // The calculator object handles all grade calculations.
        $calculator = new StudentGwaCalculator($studentName, $subjects);
        $result = $calculator->calculateGWA();

        // Save the result for the next GET request (Post/Redirect/Get).
        $_SESSION["gwa_result"] = [
            "studentName" => $studentName,
            "subjects" => array_map(function ($subject) {
                return [
                    "name" => $subject->name,
                    "grade" => $subject->grade,
                    "units" => $subject->units
                ];
            }, $subjects)
        ];

        header("Location: " . strtok($_SERVER["REQUEST_URI"], "?"));
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_SESSION["gwa_result"])) {
    $saved = $_SESSION["gwa_result"];
    unset($_SESSION["gwa_result"]);

    $studentName = $saved["studentName"];
    $rows = $saved["subjects"];
    $numberOfSubjects = count($rows);

    $savedSubjects = [];
    foreach ($rows as $row) {
        $savedSubjects[] = new Subject(
            $row["name"],
            (float)$row["grade"],
            (int)$row["units"]
        );
    }

    $calculator = new StudentGwaCalculator($studentName, $savedSubjects);
    $result = $calculator->calculateGWA();
}

// Keep the displayed subject count consistent while preserving entered data.
while (count($rows) < $numberOfSubjects) {
    $rows[] = ["name" => "", "grade" => "", "units" => ""];
}
$rows = array_slice($rows, 0, $numberOfSubjects);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grade Calculator</title>
    <style>
        :root {
            --ink: #1d2147;
            --muted: #737894;
            --purple: #6c4df6;
            --pink: #ff5da8;
            --cyan: #36c8d7;
            --orange: #ffb454;
            --line: #e8e8f6;
            --surface: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; padding: 32px 18px; min-height: 100vh; color: var(--ink);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(ellipse at 4% 5%, rgba(166, 148, 255, .28), transparent 28%),
                radial-gradient(ellipse at 96% 14%, rgba(255, 145, 195, .25), transparent 25%),
                radial-gradient(ellipse at 80% 95%, rgba(79, 221, 216, .17), transparent 25%),
                #f7f8ff;
        }
        .container { max-width: 1040px; margin: 0 auto; }
        .hero {
            position: relative; isolation: isolate; overflow: hidden; display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(210px, .8fr); align-items: center; gap: 26px;
            padding: 34px; margin-bottom: 24px; border-radius: 28px; color: #fff;
            background: linear-gradient(120deg, #4534c9 0%, #7651ed 48%, #bd4ed0 100%);
            box-shadow: 0 22px 48px rgba(94, 67, 208, .25);
        }
        .hero::before, .hero::after { content: ""; position: absolute; z-index: -1; border-radius: 50%; pointer-events: none; }
        .hero::before { width: 290px; height: 290px; right: 110px; top: -190px; background: rgba(255,255,255,.10); }
        .hero::after { width: 230px; height: 230px; right: -75px; bottom: -145px; background: rgba(255,255,255,.12); }
        .hero-copy { min-width: 0; }
        .eyebrow { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid rgba(255,255,255,.22); border-radius: 30px; background: rgba(255,255,255,.14); font-size: .73rem; font-weight: 900; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { margin: 18px 0 12px; font-size: clamp(2rem, 4.5vw, 3.1rem); line-height: 1.03; letter-spacing: -1.3px; }
        .hero p { max-width: 540px; margin: 0; color: #f0edff; line-height: 1.7; }
        .hero-card { padding: 20px; border: 1px solid rgba(255,255,255,.24); border-radius: 22px; background: rgba(255,255,255,.14); backdrop-filter: blur(8px); }
        .hero-card-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 16px; color: #f3efff; font-size: .78rem; font-weight: 800; }
        .sparkle { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 12px; color: #5d42d8; background: #fff; font-size: 1.2rem; }
        .hero-card strong { display: block; margin-bottom: 7px; font-size: 1.45rem; }
        .hero-card p { font-size: .83rem; color: #eee8ff; line-height: 1.55; }
        .scale-chips { display: flex; gap: 6px; margin-top: 17px; }
        .scale-chips span { flex: 1; padding: 7px 0; border-radius: 9px; background: rgba(255,255,255,.16); text-align: center; font-size: .75rem; font-weight: 900; }
        .scale-chips span:first-child { color: #3e328e; background: #ffe18e; }
        .panel { position: relative; padding: 27px; margin-bottom: 22px; overflow: hidden; background: var(--surface); border: 1px solid rgba(226,227,246,.95); border-radius: 23px; box-shadow: 0 13px 34px rgba(48, 48, 110, .065); }
        .panel::before { content: ""; position: absolute; left: 0; top: 0; width: 100%; height: 4px; background: linear-gradient(90deg, var(--purple), var(--pink), var(--cyan)); }
        .section-title { display: flex; align-items: center; gap: 12px; margin: 0 0 21px; font-size: 1.22rem; letter-spacing: -.25px; }
        .section-icon { display: inline-grid; place-items: center; width: 43px; height: 43px; flex: 0 0 43px; border-radius: 14px; color: #fff; background: linear-gradient(135deg, #6d51f5, #a45bea); font-size: 1.15rem; box-shadow: 0 7px 15px rgba(108,77,246,.18); }
        .field-grid { display: grid; grid-template-columns: 1.5fr 1fr; gap: 17px; }
        label { display: block; margin: 0 0 8px; color: #555a7a; font-size: .86rem; font-weight: 800; }
        input { display: block; width: 100%; min-width: 0; padding: 13px 14px; border: 1.5px solid #e1e3f3; border-radius: 13px; background: #fafaff; color: var(--ink); font: inherit; outline: none; transition: border-color .18s, box-shadow .18s, background .18s; }
        input:hover { border-color: #c9c3ff; }
        input:focus { border-color: var(--purple); background: #fff; box-shadow: 0 0 0 4px rgba(108,77,246,.12); }
        .subject-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; }
        .subject { min-width: 0; padding: 18px; border: 1px solid #e8e5ff; border-radius: 18px; background: linear-gradient(145deg, #fdfcff, #f6f7ff); transition: transform .18s, box-shadow .18s; }
        .subject:hover { transform: translateY(-2px); box-shadow: 0 10px 23px rgba(90,75,180,.08); }
        .subject-heading { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; font-weight: 900; }
        .subject-number { display: inline-grid; place-items: center; width: 34px; height: 34px; border-radius: 11px; color: #fff; background: linear-gradient(135deg, var(--purple), var(--pink)); font-size: .86rem; box-shadow: 0 5px 12px rgba(118,81,237,.22); }
        .subject label { margin-top: 13px; }
        .subject label:first-of-type { margin-top: 0; }
        .actions { display: flex; gap: 11px; flex-wrap: wrap; margin-top: 18px; }
        button { border: 0; border-radius: 13px; padding: 13px 18px; cursor: pointer; font: inherit; font-size: .9rem; font-weight: 900; transition: transform .15s, box-shadow .15s, filter .15s; }
        button:hover { transform: translateY(-2px); filter: brightness(1.03); }
        button:active { transform: translateY(0); }
        button:focus-visible { outline: 3px solid #bcb4ff; outline-offset: 3px; }
        .btn-secondary { color: #5d48d7; background: #f0edff; border: 1px solid #e4dfff; }
        .btn-primary { color: #fff; background: linear-gradient(100deg, #5c47df, #9655e8 65%, #d04fa8); box-shadow: 0 9px 19px rgba(108,77,246,.24); }
        .btn-primary::before { content: "📊 "; }
        .form-note { margin: 14px 0 0; color: var(--muted); font-size: .82rem; line-height: 1.6; }
        .result { padding: 27px; border: 1px solid #d9f1ed; border-radius: 24px; background: linear-gradient(145deg, #fff, #f4fffc); box-shadow: 0 14px 35px rgba(35,126,115,.09); overflow-wrap: anywhere; }
        .result-heading { display: flex; align-items: center; gap: 12px; margin: 0 0 6px; font-size: 1.4rem; }
        .result-subtitle { margin: 0 0 19px; color: var(--muted); line-height: 1.5; }
        .student-pill { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 19px; padding: 10px 14px; border-radius: 13px; background: #e7fbf5; color: #187c70; font-weight: 900; }
        .metrics { display: grid; grid-template-columns: 1.3fr 1fr 1fr; gap: 13px; margin: 0 0 23px; }
        .metric { position: relative; overflow: hidden; padding: 19px; border-radius: 18px; background: #fff; border: 1px solid #e3efed; }
        .metric::after { content: ""; position: absolute; width: 70px; height: 70px; right: -28px; bottom: -34px; border-radius: 50%; background: rgba(54,200,215,.12); }
        .metric-label { display: block; margin-bottom: 9px; color: var(--muted); font-size: .75rem; font-weight: 900; text-transform: uppercase; letter-spacing: .6px; }
        .metric-value { display: block; font-size: clamp(1.45rem, 3vw, 2rem); font-weight: 950; color: var(--ink); }
        .metric-gwa { color: white; border: 0; background: linear-gradient(135deg, #5944dd, #8e55e9 65%, #bb59d2); box-shadow: 0 9px 22px rgba(108,77,246,.18); }
        .metric-gwa .metric-label, .metric-gwa .metric-value { color: #fff; }
        .metric-gwa::after { background: rgba(255,255,255,.13); }
        .gwa-scale { height: 7px; margin-top: 13px; overflow: hidden; border-radius: 10px; background: rgba(255,255,255,.25); }
        .gwa-scale-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #ffe28a, #fff); }
        .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 15px; background: #fff; }
        table { width: 100%; border-collapse: collapse; min-width: 530px; }
        th, td { padding: 14px 15px; text-align: left; border-bottom: 1px solid #eeeef7; }
        th { color: #5a508c; background: #f1efff; font-size: .76rem; text-transform: uppercase; letter-spacing: .6px; }
        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:hover { background: #fbfaff; }
        .status { display: inline-block; margin-left: -8px; padding: 6px 10px; border-radius: 30px; background: #e2f8ec; color: #18764e; font-size: .77rem; font-weight: 900; white-space: nowrap; }
        .status-failed { background: #fff0f2; color: #c13f55; }
        .grade-tag { display: inline-flex; align-items: center; justify-content: center; margin: 2px 2px 0 0; padding: 5px 8px; border-radius: 8px; background: #fff0c9; color: #885b00; font-size: .7rem; font-weight: 900; line-height: 1.25; white-space: nowrap; }
        .grade-tag-high { background: #dcfce9; color: #167347; border: 1px solid #b7efcf; }
        .grade-tag-low { background: #ffe4e8; color: #b4233d; border: 1px solid #ffc4ce; }
        .status-cell { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; min-height: 42px; }
        .result h3 { margin: 0 0 16px; }
        .table-wrap + .metrics { margin-top: 16px; }
        .metrics { align-items: stretch; }
        .metric { display: flex; flex-direction: column; min-width: 0; min-height: 126px; }
        .metric-value { line-height: 1.2; }
        .metric-gwa .gwa-scale { margin-top: auto; }
        .remarks { display: inline-flex; align-items: center; gap: 9px; margin-top: 18px; padding: 12px 15px; border-radius: 13px; background: #e2f8ed; color: #176d49; font-weight: 900; }
        .remarks-failed { background: #fff0f0; color: #bb3d52; }
        .error { margin-bottom: 20px; padding: 16px 18px; border: 1px solid #ffcbd3; border-radius: 15px; background: #fff1f3; color: #a52b42; box-shadow: 0 8px 20px rgba(190,50,80,.06); }
        .error p { margin: 4px 0; }
        .footer-note { text-align: center; color: #888ba5; font-size: .78rem; padding: 20px 0 8px; }
        @media (max-width: 720px) {
            body { padding: 18px 12px; }
            .hero { grid-template-columns: 1fr; padding: 25px 22px; gap: 22px; border-radius: 22px; }
            .hero-card { max-width: 430px; }
            .panel, .result { padding: 21px; border-radius: 19px; }
            .field-grid, .subject-list { grid-template-columns: 1fr; }
            .metrics { grid-template-columns: 1fr 1fr; }
            .metric-gwa { grid-column: 1 / -1; }
        }
        @media (max-width: 390px) {
            .metrics { grid-template-columns: 1fr; }
            .metric-gwa { grid-column: auto; }
            .actions button { width: 100%; }
            .hero { padding: 22px 18px; }
            h1 { font-size: 2rem; }
        }
    </style>
</head>
    
<body>
<main class="container">
    <header class="hero">
        <div class="hero-copy">
            <span class="eyebrow">STUD GRADE TRACKER · GRADE COMPUTATION </span>
            <h1>Make every<br>grade count.</h1>
            <p>Welcome to <strong>Stud Grade Tracker</strong> — your colorful study companion for organizing subjects, checking your weighted GWA, and spotting your highest and lowest grades.</p>
        </div>
        <aside class="hero-card" aria-label="Grade scale information">
            <strong>One place. Clear results.</strong>
            <p>Enter your subject grades and units to calculate your weighted average and view your standing.</p>
            <div class="scale-chips"><span>1.00</span><span>2.00</span><span>3.00</span><span>4.00</span><span>5.00</span></div>
        </aside>
    </header>

    <form method="post" id="gwaForm">
        <section class="panel">
            <h2 class="section-title"><span class="section-icon">👨‍🎓</span> Student Information</h2>
            <div class="field-grid">
                <div>
                    <label for="student_name">Student name</label>
                    <input id="student_name" name="student_name" type="text" placeholder="Enter your name" value="<?= textCharValue($studentName) ?>" required>
                </div>
                <div>
                    <label for="number_of_subjects">Number of subjects</label>
                    <input id="number_of_subjects" name="number_of_subjects" type="number"
                           min="1" max="100" step="1" value="<?= $numberOfSubjects > 0 ? textCharValue($numberOfSubjects) : "" ?>" placeholder="Enter subject count">
                </div>
            </div>
            <div class="actions">
                <button class="btn-secondary" type="button" id="setSubjectCount">＋ Add Subjects</button>
            </div>
            <p class="form-note">Choose how many subjects you need, then fill in each subject's name, grade, and units.</p>
        </section>

        <section class="panel">
            <h2 class="section-title"><span class="section-icon">📚</span> Subject Details</h2>
            <div class="subject-list">
            <?php if (count($rows) === 0): ?>
                <p class="form-note" id="emptySubjectsMessage">No subjects added yet. Enter the number of subjects above and click “Add Subjects”.</p>
            <?php endif; ?>
            <?php foreach ($rows as $index => $row): ?>
            <section class="subject">
                <div class="subject-heading"><span class="subject-number"><?= $index + 1 ?></span> Subject <?= $index + 1 ?></div>
                <label for="subject_name_<?= $index ?>">Enter subject name:</label>
                <input id="subject_name_<?= $index ?>" name="subjects[<?= $index ?>][name]"
                       value="<?= textCharValue($row["name"] ?? "") ?>" required>

                <label for="grade_<?= $index ?>">Enter grade:</label>
                <input id="grade_<?= $index ?>" name="subjects[<?= $index ?>][grade]" type="number"
                       min="1" max="5" step="0.01" value="<?= textCharValue($row["grade"] ?? "") ?>" required>

                <label for="units_<?= $index ?>">Enter units:</label>
                <input id="units_<?= $index ?>" name="subjects[<?= $index ?>][units]" type="number"
                       min="1" step="1" value="<?= textCharValue($row["units"] ?? "") ?>" required>
            </section>
            <?php endforeach; ?>
            </div>
            <div class="actions">
                <button class="btn-primary" type="submit" name="calculate" value="1">Calculate GWA</button>
                <button class="btn-secondary" type="button" id="clearAllSubjects">Clear All Subjects</button>
            </div>
            <p class="form-note">Grades are interpreted on a 1.00–5.00 scale. Lower numeric grades are higher grades.</p>
        </section>
    </form>

    <?php if ($errors): ?>
        <div class="error" role="alert">
            <?php foreach ($errors as $error): ?><p><?= textCharValue($error) ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($calculator !== null && $result !== null): ?>
        <section class="result" id="gwaSummary">
            <h2 class="result-heading"><span class="section-icon">📋</span> Your GWA Summary</h2>
            <p class="result-subtitle">Here's the result based on your subject grades and units.</p>
            <div class="student-pill">👨‍🎓 <?= textCharValue($calculator->studentName) ?></div>
            <h3>Subject breakdown</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr><th>Subject</th><th>Grade</th><th>Units</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($calculator->subjects as $subject): ?>
                        <tr>
                            <td><strong><?= textCharValue($subject->name) ?></strong></td>
                            <td><strong><?= number_format($subject->grade, 2) ?></strong></td>
                            <td><?= textCharValue($subject->units) ?></td>
                            <td>
                                <div class="status-cell">
                                    <span class="status <?= $subject->getStatus() === "Passed" ? "" : "status-failed" ?>"><?= textCharValue($subject->getStatus()) ?></span>
                                    <?php $identifier = $calculator->getGradeIdentifier($subject); ?>
                                    <?php if ($identifier !== ""): ?>
                                        <span class="grade-tag <?= textCharValue($calculator->getGradeIdentifierClass($subject)) ?>"><?= textCharValue($identifier) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="metrics">
                <div class="metric metric-gwa">
                    <span class="metric-label">Your GWA</span>
                    <span class="metric-value"><?= number_format($result["gwa"], 2) ?></span>
                    <?php $gwaProgress = max(0, min(100, ((6 - $result["gwa"]) / 5) * 100)); ?>
                    <div class="gwa-scale" aria-label="Grade scale indicator"><div class="gwa-scale-fill" style="width: <?= number_format($gwaProgress, 2, '.', '') ?>%"></div></div>
                </div>
                <div class="metric"><span class="metric-label">Total units</span><span class="metric-value"><?= textCharValue($result["totalUnits"]) ?></span></div>
                <div class="metric"><span class="metric-label">Subjects</span><span class="metric-value"><?= count($calculator->subjects) ?></span></div>
            </div>
            <div class="remarks <?= $result["gwa"] <= 3.00 ? "" : "remarks-failed" ?>">
                <?= $result["gwa"] <= 3.00 ? "Overall Remarks: Passed" : "Overall Remarks: Failed" ?>
            </div>
        </section>
    <?php endif; ?>
    <footer class="footer-note">STUDENT GRADE TRACKER • A simple way to stay on top of your grades</footer>
</main>
