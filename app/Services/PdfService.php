<?php

namespace App\Services;

use FPDF;

// Week 8: PDF reports using FPDF
class PdfService
{
    public function exportStudentList(array $students): void
    {
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $appName = $_ENV['APP_NAME'] ?? 'Student Registration System';

        // Header
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(44, 62, 80);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, $appName . ' - Student List', 0, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, 'Generated: ' . date('d M Y H:i'), 0, 1, 'R');
        $pdf->Ln(2);

        // Table header
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(236, 240, 241);
        $cols = ['Student ID' => 25, 'First Name' => 30, 'Last Name' => 30, 'Email' => 55, 'Department' => 45, 'Year' => 18, 'Status' => 22];
        foreach ($cols as $label => $width) {
            $pdf->Cell($width, 8, $label, 1, 0, 'C', true);
        }
        $pdf->Ln();

        // Table rows
        $pdf->SetFont('Arial', '', 8);
        $fill = false;
        foreach ($students as $s) {
            $pdf->SetFillColor(248, 249, 250);
            $pdf->Cell(25, 7, $s['student_id'] ?? '', 1, 0, 'C', $fill);
            $pdf->Cell(30, 7, $s['first_name'] ?? '', 1, 0, 'L', $fill);
            $pdf->Cell(30, 7, $s['last_name'] ?? '', 1, 0, 'L', $fill);
            $pdf->Cell(55, 7, $s['email'] ?? '', 1, 0, 'L', $fill);
            $pdf->Cell(45, 7, $s['department_name'] ?? '', 1, 0, 'L', $fill);
            $pdf->Cell(18, 7, $s['enrollment_year'] ?? '', 1, 0, 'C', $fill);
            $pdf->Cell(22, 7, ucfirst($s['status'] ?? ''), 1, 0, 'C', $fill);
            $pdf->Ln();
            $fill = !$fill;

            if ($pdf->GetY() > 185) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetFillColor(236, 240, 241);
                foreach ($cols as $label => $width) {
                    $pdf->Cell($width, 8, $label, 1, 0, 'C', true);
                }
                $pdf->Ln();
                $pdf->SetFont('Arial', '', 8);
            }
        }

        // Footer
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 6, 'Total Records: ' . count($students), 0, 1, 'R');

        $pdf->Output('D', 'students_' . date('Y-m-d') . '.pdf');
        exit;
    }

    public function exportEnrollmentReport(array $enrollments): void
    {
        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $appName = $_ENV['APP_NAME'] ?? 'Student Registration System';

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(44, 62, 80);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, $appName . ' - Enrollment Report', 0, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(0, 6, 'Generated: ' . date('d M Y H:i'), 0, 1, 'R');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(236, 240, 241);
        $cols = ['Student ID' => 25, 'Student Name' => 45, 'Course' => 60, 'Credits' => 16, 'Status' => 22, 'Grade' => 16, 'Letter' => 16];
        foreach ($cols as $label => $width) {
            $pdf->Cell($width, 8, $label, 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);
        $fill = false;
        foreach ($enrollments as $e) {
            $pdf->SetFillColor(248, 249, 250);
            $name = ($e['first_name'] ?? '') . ' ' . ($e['last_name'] ?? '');
            $grade = $e['grade'] ?? null;
            $letter = $grade !== null ? letter_grade((float)$grade) : '-';
            $pdf->Cell(25, 7, $e['student_code'] ?? '', 1, 0, 'C', $fill);
            $pdf->Cell(45, 7, $name, 1, 0, 'L', $fill);
            $pdf->Cell(60, 7, ($e['course_code'] ?? '') . ' - ' . ($e['course_name'] ?? ''), 1, 0, 'L', $fill);
            $pdf->Cell(16, 7, $e['credits'] ?? '', 1, 0, 'C', $fill);
            $pdf->Cell(22, 7, ucfirst($e['status'] ?? ''), 1, 0, 'C', $fill);
            $pdf->Cell(16, 7, $grade !== null ? number_format((float)$grade, 1) : '-', 1, 0, 'C', $fill);
            $pdf->Cell(16, 7, $letter, 1, 0, 'C', $fill);
            $pdf->Ln();
            $fill = !$fill;

            if ($pdf->GetY() > 185) {
                $pdf->AddPage();
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetFillColor(236, 240, 241);
                foreach ($cols as $label => $width) {
                    $pdf->Cell($width, 8, $label, 1, 0, 'C', true);
                }
                $pdf->Ln();
                $pdf->SetFont('Arial', '', 8);
            }
        }

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 6, 'Total Records: ' . count($enrollments), 0, 1, 'R');
        $pdf->Output('D', 'enrollments_' . date('Y-m-d') . '.pdf');
        exit;
    }
}