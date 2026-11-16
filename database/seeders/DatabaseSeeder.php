<?php

namespace Database\Seeders;

use App\Models\Chama;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = User::create([
                'name' => 'Chama Admin',
                'phone' => '0700000001',
                'email' => 'admin@chama.test',
                'password' => 'password',
                'role' => 'admin',
            ]);

            $treasurer = User::create([
                'name' => 'Grace Treasurer',
                'phone' => '0700000002',
                'email' => 'treasurer@chama.test',
                'password' => 'password',
                'role' => 'treasurer',
            ]);

            $chama = Chama::create([
                'name' => 'Mumias Unity Chama',
                'monthly_amount' => 1000,
                'meeting_day' => 5,
                'admin_user_id' => $admin->id,
                'treasurer_user_id' => $treasurer->id,
            ]);

            $people = [
                ['name' => 'Grace Wanjiku', 'phone' => '0711000001', 'email' => 'grace@chama.test', 'id' => '30201001', 'role' => 'treasurer'],
                ['name' => 'Peter Otieno', 'phone' => '0711000002', 'email' => 'peter@chama.test', 'id' => '30201002', 'role' => 'member'],
                ['name' => 'Mary Akinyi', 'phone' => '0711000003', 'email' => 'mary@chama.test', 'id' => '30201003', 'role' => 'member'],
                ['name' => 'John Barasa', 'phone' => '0711000004', 'email' => 'john@chama.test', 'id' => '30201004', 'role' => 'member'],
                ['name' => 'Faith Naliaka', 'phone' => '0711000005', 'email' => 'faith@chama.test', 'id' => '30201005', 'role' => 'member'],
            ];
            $members = [];

            foreach ($people as $index => $person) {
                $user = User::create([
                    'name' => $person['name'],
                    'phone' => $person['phone'],
                    'email' => $person['email'],
                    'password' => 'password',
                    'role' => $person['role'],
                ]);
                $members[] = $chama->members()->create([
                    'user_id' => $user->id,
                    'id_number' => $person['id'],
                    'joined_date' => today()->subMonths(5 - $index),
                    'status' => 'active',
                ]);
            }

            $contributions = [
                [$members[0], 1000, now()->format('Y-m'), 'QGH7ABC001', 'confirmed'],
                [$members[1], 1000, now()->format('Y-m'), 'QGH7ABC002', 'confirmed'],
                [$members[2], 500, now()->format('Y-m'), null, 'pending'],
                [$members[3], 1000, now()->subMonth()->format('Y-m'), 'QGH7ABC004', 'confirmed'],
                [$members[4], 1500, now()->subMonth()->format('Y-m'), null, 'confirmed'],
            ];

            foreach ($contributions as [$member, $amount, $month, $code, $status]) {
                $member->contributions()->create([
                    'amount' => $amount,
                    'month' => $month,
                    'payment_method' => $code ? 'mpesa' : 'cash',
                    'mpesa_code' => $code,
                    'status' => $status,
                    'payment_date' => today()->subDays($month === now()->format('Y-m') ? 2 : 35),
                    'confirmed_by' => $status === 'confirmed' ? $treasurer->id : null,
                    'confirmed_at' => $status === 'confirmed' ? now()->subDay() : null,
                ]);
            }

            $approvedLoan = Loan::create([
                'member_id' => $members[1]->id,
                'guarantor_id' => $members[2]->id,
                'amount' => 10000,
                'interest_rate' => 10,
                'total_to_pay' => 11000,
                'balance' => 11000,
                'reason' => 'School fees',
                'status' => 'approved',
                'due_date' => today()->subDays(10),
                'reviewed_by' => $treasurer->id,
                'reviewed_at' => now()->subMonths(2),
            ]);

            LoanRepayment::create([
                'loan_id' => $approvedLoan->id,
                'amount' => 1000,
                'date' => today()->subDays(2),
                'mpesa_code' => 'QGH7REP001',
                'status' => 'confirmed',
                'confirmed_by' => $treasurer->id,
                'confirmed_at' => now()->subDay(),
            ]);

            $approvedLoan->update(['balance' => 10000]);

            Loan::create([
                'member_id' => $members[3]->id,
                'guarantor_id' => $members[4]->id,
                'amount' => 5000,
                'interest_rate' => 10,
                'total_to_pay' => 5500,
                'balance' => 0,
                'reason' => 'Small business stock',
                'status' => 'pending',
                'due_date' => today()->addMonths(3),
            ]);
        });
    }
}
