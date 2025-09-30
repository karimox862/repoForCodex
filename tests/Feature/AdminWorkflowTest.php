<?php

namespace Tests\Feature;

use App\Models\Mark;
use App\Models\Module;
use App\Models\Professor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('password'),
        ]);
    }

    public function test_admin_can_manage_entities_and_marks(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.professors.store'), [
                'name' => 'Prof Admin',
                'email' => 'prof@example.com',
                'department' => 'Mathematics',
                'password' => 'secret123',
            ])
            ->assertRedirect(route('admin.professors.index'));

        $professor = Professor::with('user')->first();
        $this->assertNotNull($professor);
        $this->assertEquals(User::ROLE_PROFESSOR, $professor->user->role);

        $this->actingAs($this->admin)
            ->post(route('admin.students.store'), [
                'first_name' => 'Student',
                'last_name' => 'Admin',
                'apogee_code' => 'APO123',
                'birth_date' => '2000-01-02',
                'email' => 'student@example.com',
            ])
            ->assertRedirect(route('admin.students.index'));

        $student = Student::first();
        $this->assertNotNull($student);

        $this->actingAs($this->admin)
            ->post(route('admin.modules.store'), [
                'code' => 'MTH101',
                'title' => 'Calculus',
                'professor_id' => $professor->id,
                'students' => [$student->id],
            ])
            ->assertRedirect(route('admin.modules.index'));

        $module = Module::with('students')->first();
        $this->assertNotNull($module);
        $this->assertTrue($module->students->contains($student));

        $mark = Mark::create([
            'module_id' => $module->id,
            'student_id' => $student->id,
            'grade' => '16',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.modules.edit', $module))
            ->post(route('admin.marks.recheck', $mark))
            ->assertRedirect(route('admin.modules.edit', $module));

        $this->assertNotNull($mark->fresh()->recheck_requested_at);

        $response = $this->actingAs($this->admin)->get(route('admin.modules.report', $module));
        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=module-MTH101-marks.csv');
        $response->assertSee('Calculus');
        $response->assertSee('16');
    }
}
