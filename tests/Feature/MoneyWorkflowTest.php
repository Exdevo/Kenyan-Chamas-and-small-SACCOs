<?php

namespace Tests\Feature;

use App\Models\Chama;
use App\Models\Contribution;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_only_see_their_own_contributions(): void
    {
        [$chama, $admin, $treasurer, $member, $other] = $this->group();
        Contribution::create([
            'member_id' => $member->member->id,
            'amount' => 1000,
            'month' => now()->format('Y-m'),
            'payment_method' => 'cash',
            'status' => 'confirmed',
            'payment_date' => today(),
            'confirmed_by' => $treasurer->id,
            'confirmed_at' => now(),
        ]);
        Contribution::create([
            'member_id' => $other->member->id,
            'amount' => 2000,
            'month' => now()->format('Y-m'),
            'payment_method' => 'cash',
            'status' => 'confirmed',
            'payment_date' => today(),
            'confirmed_by' => $treasurer->id,
            'confirmed_at' => now(),
        ]);

        $response = $this->actingAs($member)->get(route('contributions.index'));

        $response->assertOk()->assertSee('1,000.00')->assertDontSee('2,000.00');
    }

    public function test_only_the_assigned_treasurer_can_confirm_a_contribution(): void
    {
        [$chama, $admin, $treasurer, $member] = $this->group();
        $contribution = Contribution::create([
            'member_id' => $member->member->id,
            'amount' => 1000,
            'month' => now()->format('Y-m'),
            'payment_method' => 'cash',
            'status' => 'pending',
            'payment_date' => today(),
        ]);

        $this->actingAs($admin)->post(route('contributions.confirm', $contribution))->assertForbidden();
        $this->actingAs($treasurer)->post(route('contributions.confirm', $contribution))->assertRedirect();
        $this->assertDatabaseHas('contributions', [
            'id' => $contribution->id,
            'status' => 'confirmed',
            'confirmed_by' => $treasurer->id,
        ]);
    }

    public function test_loan_interest_is_ten_percent_and_repayment_updates_balance(): void
    {
        [$chama, $admin, $treasurer, $member, $guarantor] = $this->group();
        $this->actingAs($member)->post(route('loans.store'), [
            'amount' => 10000,
            'reason' => 'School fees',
            'guarantor_id' => $guarantor->member->id,
            'due_date' => today()->addMonth()->format('Y-m-d'),
        ])->assertRedirect(route('loans.index'));

        $loan = Loan::firstOrFail();
        $this->assertSame('11000.00', $loan->total_to_pay);
        $this->actingAs($treasurer)->post(route('loans.approve', $loan))->assertRedirect();
        $this->assertDatabaseHas('loans', ['id' => $loan->id, 'balance' => '11000.00']);

        $this->actingAs($member)->post(route('loans.repay', $loan), [
            'amount' => 11000,
            'date' => today()->format('Y-m-d'),
        ]);
        $repayment = LoanRepayment::firstOrFail();
        $this->actingAs($treasurer)->post(route('repayments.confirm', $repayment))->assertRedirect();
        $paidLoan = Loan::findOrFail($loan->id);
        $this->assertSame('paid', $paidLoan->status);
        $this->assertSame('0.00', $paidLoan->balance);
    }

    private function group(): array
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password', 'role' => 'admin']);
        $treasurer = User::create(['name' => 'Treasurer', 'email' => 'treasurer@example.test', 'password' => 'password', 'role' => 'treasurer']);
        $chama = Chama::create([
            'name' => 'Test Chama',
            'monthly_amount' => 1000,
            'meeting_day' => 5,
            'admin_user_id' => $admin->id,
            'treasurer_user_id' => $treasurer->id,
        ]);
        $users = [];
        foreach (['Member', 'Other member', 'Guarantor'] as $index => $name) {
            $user = User::create([
                'name' => $name,
                'email' => 'member'.$index.'@example.test',
                'password' => 'password',
                'role' => 'member',
            ]);
            $chama->members()->create([
                'user_id' => $user->id,
                'id_number' => '12345'.$index,
                'joined_date' => today(),
                'status' => 'active',
            ]);
            $users[] = $user->load('member');
        }

        return [$chama, $admin, $treasurer, ...$users];
    }
}
