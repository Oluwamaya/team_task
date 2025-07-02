<?php

namespace App\Exports;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaskExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection()
    {
        $tasks = $this->user->role === 'admin'
            ? Task::with('assignedUser')->get()
            : Task::with('assignedUser')->where('assigned_to', $this->user->id)->get();

        return $tasks->map(function ($task) {
            return [
                'Title'       => $task->title,
                'Description' => $task->description,
                'Status'      => ucfirst($task->status),
                'Assigned To' => optional($task->assignedUser)->email,
                'Due Date'    => $task->due_date,
            ];
        });
    }

    public function headings(): array
    {
        return ['Title', 'Description', 'Status', 'Assigned To (Email)', 'Due Date'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '008080']]],
        ];
    }
}
