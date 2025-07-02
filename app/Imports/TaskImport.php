<?php

namespace App\Imports;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Validator;

class TaskImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $headerSkipped = false;

        foreach ($rows as $row) {
            if (!$headerSkipped) {
                $headerSkipped = true;
                continue; // skip header row
            }

            $data = [
                'title'       => $row[0],
                'description' => $row[1],
                'status'      => strtolower($row[2]),
                'email'       => $row[3],
                'due_date'    => $row[4],
            ];

            $validator = Validator::make($data, [
                'title'       => 'required|string',
                'description' => 'required|string',
                'status'      => 'required|in:pending,in_progress,completed',
                'email'       => 'required|email|exists:users,email',
                'due_date'    => 'required|date',
            ]);

            if ($validator->fails()) {
                Log::warning('Skipped row due to validation error', ['row' => $data]);
                continue;
            }

            $user = User::where('email', $data['email'])->first();

            Task::create([
                'title'       => $data['title'],
                'description' => $data['description'],
                'status'      => $data['status'],
                'assigned_to' => $user->id,
                'due_date'    => $data['due_date'],
            ]);
        }
    }
}
