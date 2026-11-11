# Student Grade Calculator

A simple and user-friendly web-based Student Grade Tracker Calculator built with **PHP, HTML, CSS, and JavaScript**. It helps students calculate their General Weighted Average (GWA), monitor their subject grades, and identify their highest and lowest grades.

## Project Overview

The Student Grade Calculator is a web application based on an original Python grade calculator. It uses Object-Oriented Programming (OOP) in PHP to organize subject information and perform grade calculations.

Users can enter their name, specify the number of subjects, provide their grades and units, and calculate their overall GWA.

## Features

- **Student Information** – Enter the student's name and number of subjects.
- **Dynamic Subject Management** – Add subject input fields based on the specified subject count.
- **Subject Details** – Enter the subject name, grade, and corresponding units.
- **GWA Calculation** – Calculate the General Weighted Average using subject grades and units.
- **Highest Grade Identification** – Automatically identify the highest grade.
- **Lowest Grade Identification** – Automatically identify the lowest grade.
- **Subject Status** – Determine whether each subject is Passed or Not Passed.
- **Overall Remarks** – Display the student's overall passing status based on the calculated GWA.
- **Clear All Subjects** – Remove all subject details and reset the subject count.
- **Input Validation** – Validate student information, subject names, grades, and units.
- **Responsive Interface** – Provide a user-friendly interface across desktop and mobile devices.
- **Session-Based Results** – Preserve calculated results using PHP sessions and the Post/Redirect/Get pattern.

## Programming Languages Used

| Programming Languages | Purpose |
|---|---|
| PHP | Backend processing and GWA calculations |
| HTML5 | Application structure |
| CSS3 | Styling and responsive layout |
| JavaScript | Dynamic subject management and client-side validation |
| PHP Sessions | Temporary storage of calculated results |

## Grade Computation

The application calculates the General Weighted Average (GWA) using the following formula:

**GWA = Total Weighted Grade / Total Units**

Where:

- Weighted Grade = Subject Grade × Subject Units
- Total Weighted Grade = Sum of all weighted grades
- Total Units = Sum of all subject units

### Example

| Subject | Grade | Units | Weighted Grade |
|---|---:|---:|---:|
| Mathematics | 1.50 | 3 | 4.50 |
| Programming | 1.75 | 3 | 5.25 |
| English | 2.00 | 2 | 4.00 |
| **Total** | | **8** | **13.75** |

GWA = 13.75 / 8

**Final GWA: 1.72**

## Grading Rules

The application follows a grading scale from 1.00 to 5.00.

| Grade | Interpretation |
|---|---|
| 1.00 | Highest Grade |
| 2.00 | Higher Grade |
| 3.00 | Passing Grade |
| 4.00 | Lower Grade |
| 5.00 | Lowest Grade |

**Important:** Lower numerical values represent higher academic grades.

### Passing Requirements

- **Passed:** Grade is less than or equal to 3.00.
- **Not Passed:** Grade is greater than 3.00.
- **Overall Passed:** Calculated GWA is less than or equal to 3.00.
- **Overall Failed:** Calculated GWA is greater than 3.00.

## Input Validation

The application validates the following information:

| Input | Validation |
|---|---|
| Student Name | Must not be empty. |
| Number of Subjects | Must be between 1 and 100. |
| Subject Name | Must not be empty. |
| Subject Grade | Must be between 1.00 and 5.00. |
| Subject Units | Must be a positive whole number starting from 1. |

Invalid inputs display error messages to guide the user.

## Object-Oriented Programming Structure

The application uses two PHP classes:

### 1. Subject Class

Responsible for storing individual subject information.

**Properties:**
- `name` – Stores the subject name.
- `grade` – Stores the subject grade.
- `units` – Stores the number of subject units.

**Method:**
- `getStatus()` – Determines whether the subject is Passed or Not Passed.

### 2. StudentGwaCalculator Class

Responsible for handling student information and grade computations.

**Properties:**
- `studentName` – Stores the student's name.
- `subjects` – Stores an array of Subject objects.

**Methods:**
- `calculateGWA()` – Calculates the total units and weighted GWA.
- `getHighestGrade()` – Identifies the highest numerical grade standing.
- `getLowestGrade()` – Identifies the lowest numerical grade standing.
- `getGradeIdentifier()` – Identifies whether a subject has the highest or lowest grade.
- `getGradeIdentifierClass()` – Assigns the appropriate CSS class for grade identification.

## Installation and Setup

### Requirements

- PHP 7.4 or later
- A web browser
- Visual Studio Code (optional)

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/Student-Grade-Calculator.git
```

### 2. Navigate to the Project Folder

```bash
cd Student-Grade-Calculator
```

### 3. Run the PHP Development Server

```bash
php -S localhost:8000
```

### 4. Open the Application

Visit the following address in your web browser:

```text
http://localhost:8000
```

You can now use the STUD Grade Tracker Calculator.

## Project Structure

```text
student-grade-calculator/
│
├── index.php
└── README.md
```

- `index.php` – Contains the PHP classes, grade calculations, HTML structure, CSS styling, JavaScript functionality, and form validation.
- `README.md` – Contains the project documentation and setup instructions.

## Notes

- The application does not require a database.
- Calculated results are temporarily stored using PHP sessions.
- The application uses the PHP built-in development server for local testing.
- The project is designed for straightforward grade computation and student grade monitoring.

## License

This project is intended for educational purposes and academic use.
